<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Auth extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->helper('url');
    }

    public function session($provider) {
        $this->load->helper('url_helper');
        // $this->load->library('user_agent');
        //$this->load->spark('oauth2/0.3.1');

        $provider = $this->oauth2->provider($provider, array(
            'id' => config_item('github_oauth_id'),
            'secret' => config_item('github_oauth_secret'),
        ));

        if (!$this->input->get('code')) {

            // By sending no options it'll come back here
            redirect($provider->authorize(array('redirect_uri' => config_item('github_oauth_redirect'))));
        } else {
            // Howzit?
            try {

                $token = $provider->access($_GET['code'], array('redirect_uri' => config_item('github_oauth_redirect')));

                $user = $provider->get_user_info($token);

                // Here you should use this information to A) look for a user B) help a new user sign up with existing data.
                // If you store it all in a cookie and redirect to a registration page this is crazy-simple.
                // Save token and token secret to session and database?
                // Currently I'm not totally sure why a we'd need to save the token to a separate db table if its saved in
                // the session and we get it with every login. Does the token ever change?

                $users_auth = array('provider_user_id' => $user['uid'],
                    'token' => $token->access_token,
                    'provider' => 'github');

                $user_data = array('username' => $user['nickname'],
                    'name_full' => $user['name'],
                    'username_url' => $user['nickname'],
                    'provider_url' => $user['urls']['GitHub']);

                // check to see if we already have a user in our users_auth table as well as corresponding id in users table that matches the github userid of this person
                // Saving to users_auth if not already found, should save to session userdata too.

                $user = $this->check_user($user['nickname']);

                if (empty($user)) {

                    $pre_approved_admins = $this->config->item('pre_approved_admins');
                    $pre_approved_admins = array_map('strtolower', $pre_approved_admins);

                    if (array_search(strtolower($user_data['username']), $pre_approved_admins) !== false) {
                        $user_data['permissions'] = 'admin';
                    }

                    $user = array_merge($users_auth, $user_data);
                    $this->db->insert('users_auth', $user);
                } else {
                    $user_data = array_merge($user, $user_data);
                }

                $user_data['name_full'] = (empty($user_data['name_full'])) ? $user_data['nickname'] : $user_data['name_full'];

                //$this->db->insert('users_auth', $users_auth) ;

                $this->session->set_userdata($users_auth);
                $this->session->set_userdata($user_data);

                // Send user info to Apache for special access logging
                apache_note("user", $user_data['nickname'] ? $user_data['nickname'] : 'Anonymous');


                // if we don't already have this user, then direct to registration page with prefilled values (username, email if provided) - will need to check to see if username or email address are already in use too
                // if we already have this user then we make sure session variables are set and redirect them to their dashboard page. Every other page checks their session to make sure they're logged in and legit

                redirect('account');


                /*
                  echo "<pre>Auth Data: \n\n";
                  var_dump($users_auth);

                  echo "<pre>User Data: \n\n";
                  var_dump($user_data);

                 */




                /* Save in a database for future use. */
                // $_SESSION['oauth_access_token']	 		= $token['access_token'];
                // $_SESSION['oauth_access_token_secret'] 	= $tokens['oauth_token_secret'];
            } catch (OAuth2_Exception $e) {
                show_error('That didnt work: ' . $e);
            }
        }
    }

    public function check_user($username) {
        $query = $this->db->get_where('users_auth', array('username_url' => $username));

        return $query->row_array();
    }

    public function logout() {
        $this->session->sess_destroy();
        $this->load->view('logout_view');
    }

    // need a function to check for login status, I think this can just verify the value of a session variable
}

?>
