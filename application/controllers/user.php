<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->load->helper('url');
    }


    public function metadata()
    {

//		try {
//			#$auth = new OneLogin_Saml2_Auth($settingsInfo);
//			#$settings = $auth->getSettings();
//			// Now we only validate SP settings
//			$settings = new OneLogin_Saml2_Settings(config_item('saml'), true);
//			$metadata = $settings->getSPMetadata();
//			$errors = $settings->validateMetadata($metadata);
//			if (empty($errors)) {
//				header('Content-Type: text/xml');
//				echo $metadata;
//			} else {
//				throw new OneLogin_Saml2_Error(
//					'Invalid SP metadata: ' . implode(', ', $errors),
//					OneLogin_Saml2_Error::METADATA_SP_INVALID
//				);
//			}
//		} catch (Exception $e) {
//			echo $e->getMessage();
//		}
    }

    public function login()
    {
//		$auth = new OneLogin_Saml2_Auth(config_item('saml'));
//		$auth->login();


        $as = new SimpleSAML_Auth_Simple('max');
        $as->requireAuth();

        redirect('offices');
    }

    public function acs()
    {
        /**
         * Assertion consumer service handler for SAML 2.0 SP authentication client.
         */

        $sourceId = 'max';
        $source = SimpleSAML_Auth_Source::getById($sourceId, 'sspmod_saml_Auth_Source_SP');
        $spMetadata = $source->getMetadata();

        try {
            $b = SAML2_Binding::getCurrentBinding();
        } catch (Exception $e) { // TODO: look for a specific exception
            // This is dirty. Instead of checking the message of the exception, SAML2_Binding::getCurrentBinding() should throw
            // an specific exception when the binding is unknown, and we should capture that here
            if ($e->getMessage() === 'Unable to find the current binding.') {
                throw new SimpleSAML_Error_Error('ACSPARAMS', $e, 400);
            } else {
                throw $e; // do not ignore other exceptions!
            }
        }

        if ($b instanceof SAML2_HTTPArtifact) {
            $b->setSPMetadata($spMetadata);
        }

        $response = $b->receive();
        if (!($response instanceof SAML2_Response)) {
            throw new SimpleSAML_Error_BadRequest('Invalid message received to AssertionConsumerService endpoint.');
        }

        $idp = $response->getIssuer();
        if ($idp === null) {
            // no Issuer in the response. Look for an unencrypted assertion with an issuer
            foreach ($response->getAssertions() as $a) {
                if ($a instanceof SAML2_Assertion) {
                    // we found an unencrypted assertion, there should be an issuer here
                    $idp = $a->getIssuer();
                    break;
                }
            }
            if ($idp === null) {
                // no issuer found in the assertions
                throw new Exception('Missing <saml:Issuer> in message delivered to AssertionConsumerService.');
            }
        }

        $session = SimpleSAML_Session::getSessionFromRequest();
        $prevAuth = $session->getAuthData($sourceId, 'saml:sp:prevAuth');
        if ($prevAuth !== null && $prevAuth['id'] === $response->getId() && $prevAuth['issuer'] === $idp) {
            /* OK, it looks like this message has the same issuer
             * and ID as the SP session we already have active. We
             * therefore assume that the user has somehow triggered
             * a resend of the message.
             * In that case we may as well just redo the previous redirect
             * instead of displaying a confusing error message.
             */
            SimpleSAML_Logger::info(
                'Duplicate SAML 2 response detected - ignoring the response and redirecting the user to the correct page.'
            );
            if (isset($prevAuth['redirect'])) {
                \SimpleSAML\Utils\HTTP::redirectTrustedURL($prevAuth['redirect']);
            }

            SimpleSAML_Logger::info('No RelayState or ReturnURL available, cannot redirect.');
            throw new SimpleSAML_Error_Exception('Duplicate assertion received.');
        }

        $idpMetadata = array();

        $stateId = $response->getInResponseTo();
        if (!empty($stateId)) {
            // this is a response to a request we sent earlier
            $state = SimpleSAML_Auth_State::loadState($stateId, 'saml:sp:sso');

            // check that the authentication source is correct
            assert('array_key_exists("saml:sp:AuthId", $state)');
            if ($state['saml:sp:AuthId'] !== $sourceId) {
                throw new SimpleSAML_Error_Exception(
                    'The authentication source id in the URL does not match the authentication source which sent the request.'
                );
            }

            // check that the issuer is the one we are expecting
            assert('array_key_exists("ExpectedIssuer", $state)');
            if ($state['ExpectedIssuer'] !== $idp) {
                $idpMetadata = $source->getIdPMetadata($idp);
                $idplist = $idpMetadata->getArrayize('IDPList', array());
                if (!in_array($state['ExpectedIssuer'], $idplist)) {
                    throw new SimpleSAML_Error_Exception(
                        'The issuer of the response does not match to the identity provider we sent the request to.'
                    );
                }
            }
        } else {
            // this is an unsolicited response
            $state = array(
                'saml:sp:isUnsolicited' => true,
                'saml:sp:AuthId' => $sourceId,
                'saml:sp:RelayState' => \SimpleSAML\Utils\HTTP::checkURLAllowed(
                    $spMetadata->getString(
                        'RelayState',
                        $response->getRelayState()
                    )
                ),
            );
        }

        SimpleSAML_Logger::debug('Received SAML2 Response from ' . var_export($idp, true) . '.');

        if (empty($idpMetadata)) {
            $idpMetadata = $source->getIdPmetadata($idp);
        }

        try {
            $assertions = sspmod_saml_Message::processResponse($spMetadata, $idpMetadata, $response);
        } catch (sspmod_saml_Error $e) {
            // the status of the response wasn't "success"
            $e = $e->toException();
            SimpleSAML_Auth_State::throwException($state, $e);
        }


        $authenticatingAuthority = null;
        $nameId = null;
        $sessionIndex = null;
        $expire = null;
        $attributes = array();
        $foundAuthnStatement = false;
        foreach ($assertions as $assertion) {

            // check for duplicate assertion (replay attack)
            $store = SimpleSAML_Store::getInstance();
            if ($store !== false) {
                $aID = $assertion->getId();
                if ($store->get('saml.AssertionReceived', $aID) !== null) {
                    $e = new SimpleSAML_Error_Exception('Received duplicate assertion.');
                    SimpleSAML_Auth_State::throwException($state, $e);
                }

                $notOnOrAfter = $assertion->getNotOnOrAfter();
                if ($notOnOrAfter === null) {
                    $notOnOrAfter = time() + 24 * 60 * 60;
                } else {
                    $notOnOrAfter += 60; // we allow 60 seconds clock skew, so add it here also
                }

                $store->set('saml.AssertionReceived', $aID, true, $notOnOrAfter);
            }


            if ($authenticatingAuthority === null) {
                $authenticatingAuthority = $assertion->getAuthenticatingAuthority();
            }
            if ($nameId === null) {
                $nameId = $assertion->getNameId();
            }
            if ($sessionIndex === null) {
                $sessionIndex = $assertion->getSessionIndex();
            }
            if ($expire === null) {
                $expire = $assertion->getSessionNotOnOrAfter();
            }

            $attributes = array_merge($attributes, $assertion->getAttributes());

            if ($assertion->getAuthnInstant() !== null) {
                // assertion contains AuthnStatement, since AuthnInstant is a required attribute
                $foundAuthnStatement = true;
            }
        }

        //        array(10) {
//        ["maxUserClassification"]=>
//          array(1) {
//            [0]=>
//            string(7) "FEDERAL"
//          }
//          ["maxOrgTag"]=>
//          array(1) {
//            [0]=>
//            string(5) "(GSA)"
//          }
//          ["maxAgency"]=>
//          array(1) {
//            [0]=>
//            string(31) "General Services Administration"
//          }
//          ["maxGroupList"]=>
//          array(1) {
//            [0]=>
//            string(50) "AGY-GSA,EXECUTIVE_BRANCH,MAX-SHORTURL.USER,MAXINFO"
//          }
//          ["maxId"]=>
//          array(1) {
//            [0]=>
//            string(7) "A929609"
//          }
//          ["maxFirstName"]=>
//          array(1) {
//            [0]=>
//            string(7) "Kishore"
//          }
//          ["maxSecurityLevelList"]=>
//          array(1) {
//            [0]=>
//            string(8) "standard"
//          }
//          ["maxLastName"]=>
//          array(1) {
//            [0]=>
//            string(7) "Vuppala"
//          }
//          ["maxEmail"]=>
//          array(1) {
//            [0]=>
//            string(23) "kishore.vuppala@gsa.gov"
//          }
//          ["maxBureau"]=>
//          array(1) {
//            [0]=>
//            string(31) "General Services Administration"
//          }
//        }

//        Let's set CodeIgniter user
        $userdata = array();
        $userdata['username'] = $attributes['maxEmail'][0];
        $userdata['name_full'] = $attributes['maxFirstName'][0].' '.$attributes['maxLastName'][0];
        $userdata['permissions'] = 'user';
        $admin_emails = array(
            'kishore.vuppala@gsa.gov'
        );
        if (in_array($userdata['username'], $admin_emails)) {
            $userdata['permissions'] = 'admin';
        }
        $userdata['provider_url'] = 'max.gov';
        $this->session->set_userdata($userdata);
        $this->session->set_userdata($attributes);


        if ($expire !== null) {
            $logoutExpire = $expire;
        } else {
            // just expire the logout association 24 hours into the future
            $logoutExpire = time() + 24 * 60 * 60;
        }

// register this session in the logout store
        sspmod_saml_SP_LogoutStore::addSession($sourceId, $nameId, $sessionIndex, $logoutExpire);

// we need to save the NameID and SessionIndex for logout
        $logoutState = array(
            'saml:logout:Type' => 'saml2',
            'saml:logout:IdP' => $idp,
            'saml:logout:NameID' => $nameId,
            'saml:logout:SessionIndex' => $sessionIndex,
        );
        $state['LogoutState'] = $logoutState;
        $state['saml:AuthenticatingAuthority'] = $authenticatingAuthority;
        $state['saml:AuthenticatingAuthority'][] = $idp;
        $state['PersistentAuthData'][] = 'saml:AuthenticatingAuthority';

        $state['saml:sp:NameID'] = $nameId;
        $state['PersistentAuthData'][] = 'saml:sp:NameID';
        $state['saml:sp:SessionIndex'] = $sessionIndex;
        $state['PersistentAuthData'][] = 'saml:sp:SessionIndex';
        $state['saml:sp:AuthnContext'] = $assertion->getAuthnContext();
        $state['PersistentAuthData'][] = 'saml:sp:AuthnContext';

        if ($expire !== null) {
            $state['Expire'] = $expire;
        }

// note some information about the authentication, in case we receive the same response again
        $state['saml:sp:prevAuth'] = array(
            'id' => $response->getId(),
            'issuer' => $idp,
        );
        if (isset($state['SimpleSAML_Auth_Source.ReturnURL'])) {
            $state['saml:sp:prevAuth']['redirect'] = $state['SimpleSAML_Auth_Source.ReturnURL'];
        } elseif (isset($state['saml:sp:RelayState'])) {
            $state['saml:sp:prevAuth']['redirect'] = $state['saml:sp:RelayState'];
        }

        $state['PersistentAuthData'][] = 'saml:sp:prevAuth';

        $source->handleResponse($state, $idp, $attributes);
        assert('FALSE');


//        $as = new SimpleSAML_Auth_Simple('max');
//
//        $attributes = $as->getAttributes();
//
//        $this->session->set_userdata($attributes);
//        redirect('/');
//        print_r($attributes);
    }

    public function logout()
    {
//		$auth = new OneLogin_Saml2_Auth(config_item('saml'));
//
//		$returnTo = null;
//		$paramters = array();
//		$nameId = null;
//		$sessionIndex = null;
//		if (isset($_SESSION['samlNameId'])) {
//			$nameId = $_SESSION['samlNameId'];
//		}
//		if (isset($_SESSION['samlSessionIndex'])) {
//			$sessionIndex = $_SESSION['samlSessionIndex'];
//		}
//
//		$auth->logout($returnTo, $paramters, $nameId, $sessionIndex);

        $this->session->sess_destroy();
    }
}
