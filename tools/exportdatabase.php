#!/usr/bin/php
<?php
/*
 * PHP script to perform a database cleanup and export it
 *
 * Usage: 
 *      ./exportdatabase.php <tables>
 */

error_reporting(E_ERROR | E_WARNING | E_PARSE);

if (in_array($argv[1], array('--help', '-help', '-h', '-?'))) {
?>

PHP script to perform a database cleanup and export it

  Usage: 
        <?php echo $argv[0]; ?> <option>

  <option>
        all     - does all other commands
        clean   - cleans database
        export  - exports database

<?php
} else {
    if(!isset($argv[1])) 
        $command = 'all';
    else 
        $command = $argv[1];

    if(!defined('TOOLS_DIRECTORY')) define('TOOLS_DIRECTORY', dirname(__FILE__));

    require_once(TOOLS_DIRECTORY.'/../inc/config.inc.php');
    require_once(TOOLS_DIRECTORY.'/../inc/const.inc.php');

    /*
     * Functions
     */
    function clean() {
        cleanUsers();
        emptyLogin();
        emptyArticleVists();
    }

    function cleanUsers() {
        echo "--------------- Clean User Table ----------------\n";
        /*
         * Remove all users that haven't interacted with Felix Online 
         * (made an article, commented, liked a comment, uploaded an image or is a category_author)
         */
        mysql_query('DELETE FROM user 
            WHERE (
                SELECT COUNT(*) FROM article_author 
                WHERE user.user = article_author.author
            ) = 0 
            AND (
                SELECT COUNT(*) FROM comment 
                WHERE comment.user = user.user
            ) = 0 
            AND (
                SELECT COUNT(*) FROM comment_like 
                WHERE comment_like.user = user.user
            ) = 0 
            AND (
                SELECT COUNT(*) FROM image 
                WHERE image.user = user.user
            ) = 0 
            AND (
                SELECT COUNT(*) FROM category_author 
                WHERE category_author.user = user.user
            ) = 0');
        echo "------------- End Clean User Table --------------\n";
    }

    function emptyLogin() {
        echo "--------------- Empty Login Table ---------------\n";
        mysql_query('TRUNCATE TABLE login');
    }

    function emptyArticleVists() {
        echo "---------- Empty Article Visits Table -----------\n";
        mysql_query('TRUNCATE TABLE article_visit');
    }

    function export() {
        echo "----- Saving Database To media_felix.sql --------\n";
        global $dbname, $host, $user, $pass;
        $exec = 'mysqldump -h '.$host.' -u '.$user.' -p'.$pass.' '.$dbname.' > '.TOOLS_DIRECTORY.'/../media_felix.sql';
        echo shell_exec($exec);
    }

    echo "---------------- Starting Backup ----------------\n";

    switch($command) {
        case 'all':
            clean();
            export();
            break;
        case 'clean':
            clean();
            break;
        case 'export':
            export();
            break;
    }

    echo "--------------------- END -----------------------\n";
}

?>