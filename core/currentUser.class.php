<?php
/*
 * Current User class
 */
class CurrentUser extends User {
    protected $session; // current session id
    protected $ip; // user ip address
    
    /*
     * Create current user object
     * Store session id and ip address into object
     */
    function __construct() {
        session_name("felix"); // set session name
        session_start(); // start session
        $this->session = session_id(); // store session id into $session variable
        $this->ip = $_SERVER['REMOTE_ADDR'];
        if($this->isLoggedIn()) {
            $this->setUser($this->isLoggedIn());
        }
    }

    /*
     * Public: Resets the session cookie, regenerating its ID, and ensures old session data is removed
     */
    function resetToGuest()
    {
        // the true parameter clears the current session
        session_destroy();
        session_start();
        session_regenerate_id(true);
        
        $this->session = session_id();
    }

    /*
     * Private: Check if user has just logged in
     */

    /*
     * Public: Check if user is currently logged in
     *
     * Returns boolean
     */
    public function isLoggedIn() {
        if($_SESSION['felix']['loggedin'] && $this->isSessionRecent()){
            return $_SESSION['felix']['uname'];
        } else {
            // n.b. we don't reset the session here unless we have a bad one
            return false;
        }
    }

    /*
     * Public: Check if the session is recent (the last visited time is updated
     * on every visit, if this is greater than two hours then we need to log in
     * again, unless the cookie is valid
     */
    public function isSessionRecent()
    {
        if (!$_SESSION['felix']['loggedin']) {
            return false; // If we have no session, this method is meaningless.
        }

        global $db;
        $sql = "SELECT
                    TIMESTAMPDIFF(SECOND,timestamp,NOW()) AS timediff,
                    ip,
                    browser
                FROM `login`
                WHERE session_id='".$this->session."'
                AND logged_in=1
                AND valid=1
                ORDER BY timediff ASC
                LIMIT 1
        ";

        $user = $db->get_row($sql);

        if (
            $user->timediff <= SESSION_LENGTH 
            && $user->ip == $_SERVER['REMOTE_ADDR']
            && $user->browser == $_SERVER['HTTP_USER_AGENT']
        ) {
            return true;
        } else {
            // FIXME: Cookies
            $this->resetToGuest(); // Clear invalid session data
            // FIXME: clear old sessions from db?
            return false;
        }
    }

    /*
     * Public: Set user
     */
    public function setUser($username) {
        $username = strtolower($username);
        parent::__construct($username);
        /* update user details */
        $this->updateName();
        $sql = "INSERT INTO `user` 
            (user,name,visits,ip) 
            VALUES (
                '$username',
                '".$this->getName()."',
                1,
                '".$_SERVER['REMOTE_ADDR']."'
            ) 
            ON DUPLICATE KEY 
            UPDATE 
                name='".$this->getName()."',
                visits=visits+1,
                ip='".$_SERVER['REMOTE_ADDR']."',
                timestamp=NOW()";
                // note that this updated the last access time and the ip
                // of the last access for this user, this is separate from the
                // session
        return $this->db->query($sql);
    }

    public function getSession() {
        return $this->session;
    }

    public function getUser() {
        if($this->fields['user']) {
            return $this->fields['user'];
        } else {
            return false;
        }
    }
    /*
     * Update user's name from ldap
     */
    private function updateName() {
        if(!LOCAL) {
            $ds = ldap_connect("addressbook.ic.ac.uk");
            $r = ldap_bind($ds);
            $justthese = array("gecos");
            $sr = ldap_search($ds, "ou=People, ou=everyone, dc=ic, dc=ac, dc=uk", "uid=$uname", $justthese);
            $info = ldap_get_entries($ds, $sr);
            if ($info["count"] > 0)
                $this->setName($info[0]['gecos'][0]);
            else
                return false;
        }
    }
}
?>
