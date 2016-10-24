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
    }

    public function acs()
    {
        $as = new SimpleSAML_Auth_Simple('max');

        $attributes = $as->getAttributes();
        print_r($attributes);
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
    }
}
