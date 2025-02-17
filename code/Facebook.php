<?php
/**
 * Facebook.php
 *
 * @author Bram de Leeuw
 * Date: 25/11/16
 */


class Facebook extends Object {

    private static $app_id = '';

    private static $app_secret = '';

    private static $page_id = '';

    protected $facebook;

    /**
     * Facebook constructor.
     */
    public function __construct()
    {
        if (!isset($this->facebook)) $this->facebook = new Facebook\Facebook([
            'app_id' => self::config()->get('app_id'),
            'app_secret' => self::config()->get('app_secret'),
            'default_graph_version' => 'v2.5',
        ]);

        // TODO check if token and if valid, else stop facebook operations and notify user silently
        if ($token = Facebook::get_access_token()) {
            $this->facebook->setDefaultAccessToken($token);
        }
        
        parent::__construct();
    }


    /**
     * Return a facebook instance
     *
     * @return \Facebook\Facebook
     */
    public function instance() {
        return $this->facebook;
    }


    /**
     * Get a graph node
     *
     * @param $path
     * @param array $params
     * @return \Facebook\FacebookResponse|null
     */
    public function get($path, array $params = []) {
        return $this->facebook->sendRequest('GET', $path, $params);
    }


    /**
     * Get the page data
     *
     * @param null $node
     * @param array $params
     * @return \Facebook\FacebookResponse|null
     */
    public function getPage($node = null, array $params = []) {
        $pageID = self::config()->get('page_id');
        return $this->get("/$pageID/$node", $params);
    }


    /**
     * Get a event list
     *
     * @return \Facebook\FacebookResponse|null
     */
    public function getPageEvents() {
        return $this->getPage('events');
    }



    /**
     * Get a available access token,
     * if there is a current user with an access token return that one first
     *
     * @return string|null
     */
    private static function get_access_token() {
        $members = Member::get()->filter(array(
            'FB_LongLivedAccessToken:not' => '',
            'FB_LongLivedAccessTokenValidUntil:GreaterThan' => date('Y-m-d')
        ));

        if ($member = Member::currentUser()) {
            return $member->getFBAccessToken();
        } else if ($members->count() && $member = $members->first()) {
            return $member->getFBAccessToken();
        } else {
            // TODO Prompt the user to reauthenticate
            user_error('No access token available');
            return null;
        }
    }


    /**
     * Get a config var
     *
     * @param $var
     * @return array|scalar
     * /
    private static function get_config($var) {
        return Config::inst()->get('Facebook', $var);
    } //*/
}