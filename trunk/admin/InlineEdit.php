<?php

/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */
class InlineEdit {
    /**
     * Instance of McAdmin
     */
    private $mcAdmin;


    public function __construct( $minicomposerAdminInstance ) {
        $this->mcAdmin = $minicomposerAdminInstance;
        // register ajax-tasks for inline-edit
        add_action( 'wp_ajax_getColumnContent', array( $this, 'ajaxGetColumnContent' ) );
        add_action( 'wp_ajax_nopriv_getcolumncontent', array( $this, 'ajaxGetColumnContent' ) );
        add_action( 'wp_ajax_changeColumnContent', array( $this, 'ajaxChangeColumnContent' ) );
        add_action( 'wp_ajax_nopriv_changeColumnContent', array( $this, 'ajaxChangeColumnContent' ) );

        add_action( 'wp_ajax_getTitleContent', array( $this, 'ajaxGetTitleContent' ) );
        add_action( 'wp_ajax_nopriv_getTitleContent', array( $this, 'ajaxGetTitleContent' ) );
        add_action( 'wp_ajax_changeTitleContent', array( $this, 'ajaxChangeTitleContent' ) );
        add_action( 'wp_ajax_nopriv_changeTitleContent', array( $this, 'ajaxChangeTitleContent' ) );
    }

    /**
     * Ajax: Gets title of a post for inline-edit
     */
    public function ajaxGetTitleContent() {
        header( 'Content-Type: application/json' );

        $postid = filter_input( INPUT_GET, 'postid' );

        $output = array(
            'postid' => $postid,
            'success' => false,
        );

        if ( empty( $postid ) || !$this->checkRights( $postid ) ) {
            die( json_encode( $output ) );
        }

        $post = get_post( $postid );
        $output['content'] = $post->post_title;
        $output['postslug'] = $post->post_name;
        $output['success'] = true;

        die( json_encode( $output ) );
    }

    /**
     * Ajax: Change content of a column for inline-edit
     */
    public function ajaxChangeTitleContent() {
        header( 'Content-Type: application/json' );

        /*
         * Params
         *  newContent
         *  postid
         */

        $postid = filter_input( INPUT_POST, 'postid' );
        $newContent = filter_input( INPUT_POST, 'newcontent' );


        $output = array(
            'postid' => $postid,
            'newContent' => $newContent,
            'success' => false,
        );

        if ( empty( $postid ) || !$this->checkRights( $postid ) ) {
            die( json_encode( $output ) );
        }

        $post = get_post( $postid );
        $output['postslug'] = $post->post_name;

        wp_update_post( array(
            'ID' => $postid,
            'post_title' => $newContent,
        ) );;

        $output['success'] = true;
        #$output[ 'parsedContent' ] = do_shortcode( $newContent );

        die( json_encode( $output ) );
    }

    /**
     * Ajax: Gets content of a single column for inline-edit
     */
    public function ajaxGetColumnContent() {
        header( 'Content-Type: application/json' );
        /*
         * Params
         *  postid
         *  columnid
         */

        $postid = filter_input( INPUT_GET, 'postid' );
        $columnid = filter_input( INPUT_GET, 'columnid' );

        $output = array(
            'columnid' => $columnid,
            'postid' => $postid,
            'success' => false,
        );

        if ( empty( $postid ) || !isset( $columnid ) || !$this->checkRights( $postid ) ) {
            die( json_encode( $output ) );
        }

        $composerRows = get_post_meta( $postid, 'minicomposerColumns', true );

        // get row-array
        $rows = ( $composerRows );

        // get list of all columns
        $columnList = \Admin\MinicomposerAdmin::getColumnContentList( $rows );

        $post = get_post( $postid );
        $output['postslug'] = $post->post_name;

        // get selected column
        if ( isset( $columnList[$columnid] ) ) {
            $columnContent = $columnList[$columnid];
            $output['success'] = true;
            $output['content'] = $columnContent;
        }

        die( json_encode( $output ) );
    }

    /**
     * Ajax: Change content of a column for inline-edit
     */
    public function ajaxChangeColumnContent() {
        ini_set( 'display_errors', true );
        header( 'Content-Type: application/json' );

        /*
         * Params
         *  newContent
         *  postid
         *  columnid
         */

        $postid = filter_input( INPUT_POST, 'postid' );
        $columnid = filter_input( INPUT_POST, 'columnid' );
        $newContent = filter_input( INPUT_POST, 'newcontent' );


        $output = array(
            'columnid' => $columnid,
            'postid' => $postid,
            'newContent' => $newContent,
            'success' => false,
        );

        if ( empty( $postid ) || !isset( $columnid ) || !$this->checkRights( $postid ) ) {
            die( json_encode( $output ) );
        }

        $post = get_post( $postid );
        $output['postslug'] = $post->post_name;

        $composerRows = get_post_meta( $postid, 'minicomposerColumns', true );
        // get row-array
        $rows = $composerRows;

        // change content of column
        $rows = \Admin\MinicomposerAdmin::changeColumnContent( $rows, $columnid, $newContent );
        $newRows = json_decode( json_encode( $rows ) ); // need copy, cause getColumnContent removes shortcodes
        $output['newRows'] = $rows;

        remove_action( 'save_post', array( $this, 'savePostMeta' ) );
        $postContent = $this->mcAdmin->getColumnContent( $rows );

        wp_update_post( array(
            'ID' => $postid,
            'post_content' => $postContent,
        ) );
        add_action( 'save_post', array( $this, 'savePostMeta' ), 10, 2 );

        $update = update_post_meta( $postid, 'minicomposerColumns', $newRows );

        $output['success'] = $update;

        die( json_encode( $output ) );
    }

    /**
     * Checks if user can edit_page/edit_post
     *
     * @int/object $post
     * @return bool
     */
    public function checkRights( $post ) {
        if ( is_numeric( $post ) ) {
            $post = get_post( $post );
        }

        if ( $post->post_type == 'page' ) {
            if ( current_user_can( 'edit_page', $post->ID ) ) {
                return true;
            }
        } else {
            if ( current_user_can( 'edit_post', $post->ID ) ) {
                return true;
            }
        }

        return false;
    }
}