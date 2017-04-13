<?php

/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */
class MiniComposerThemeCustomizer {
    private $sectionId;
    private $textdomain;
    private $fields;

    public function __construct() {
        $id = 'minicomposer';
        $this->textdomain = $id;
        $this->sectionId = $id;

        $this->fields = array();

        $this->fields['minicomposer-base'] =
            array(
                'title' => 'Base-Settings',
                'fields' => array(
                    array(
                        'id' => 'globalPadding',
                        'label' => 'Column-Padding',
                        'transport' => 'refresh',
                    ),
                    array(
                        'id' => 'globalGutter',
                        'label' => 'Column-Gutter',
                    ),
                    array(
                        'id' => 'globalMinHeight',
                        'label' => 'Column-Min-Height',
                    ),
                    array(
                        'id' => 'globalColumnMargin',
                        'label' => 'Column-Margin-Bottom',
                    ),
                    array(
                        'id' => 'globalRowMargin',
                        'label' => 'Row-Magin-Bottom',
                    ),
                ),
            );

        $this->fields['minicomposer-expert'] =
            array(
                'title' => 'Expert-Settings',
                'fields' => array(
                    array(
                        'id' => 'useBootstrap',
                        'label' => 'Use bootstrap instead of foundation',
                        'type' => 'checkbox',
                    ),
                    array(
                        'id' => 'embedFromCDN',
                        'type' => 'checkbox',
                        'label' => 'Load Foundation/Bootstrap from CDN (only use if your theme doesn\'t already include it)',
                    ),
                ),
            );


        $this->fields['minicomposer-admin'] =
            array(
                'title' => 'Admin-Settings',
                'fields' => array(
                    'columnAdminStyle' => array(
                        'id' => 'columnAdminStyle',
                        'type' => 'textarea',
                        'label' => 'Column-Style for admin',
                        'description' => 'Only for admin-view',
                    ),
                    'columnAdminFont' => array(
                        'id' => 'columnAdminFont',
                        'type' => 'textarea',
                        'label' => 'Column-Font for admin',
                        'description' => 'Only for admin-view',
                    ),
                ),
            );
    }

    public function actionCustomizeRegister( $wp_customize ) {

        $prefix = 'minicomposer_';
        $wp_customize->add_panel( 'minicomposer-panel', array(
            'title' => __( 'MiniComposer' ),
            'section' => 'minicomposer',
        ) );




        foreach ( $this->fields as $sectionId => $section ) {
            $wp_customize->add_section( $sectionId, array(
                'title' => __( $section['title'], $this->textdomain ),
                'panel' => 'minicomposer-panel',
            ) );

            foreach ( $section['fields'] as $fieldId => $field ) {
                $settingId = $prefix . ( !is_numeric($fieldId) ? $fieldId : $field['id'] );
                $controlId = $settingId . '-control';

                $wp_customize->add_setting( $settingId, array(
                    'default' => !empty( $field['default'] ) ? $field['default'] : '',
                    'transport' => !empty( $field['transport'] ) ? $field['transport'] : 'refresh',
                ) );

                $wp_customize->add_control( $controlId, array(
                    'label' => __( $field['label'], $this->textdomain ),
                    'section' => $sectionId,
                    'type' => !empty( $field['type'] ) ? $field['type'] : 'text',
                    'settings' => $settingId,
                    'description' => !empty( $field['description'] ) ? __( $field['description'], $this->textdomain ) : '',
                    'choices' => !empty( $field['choices'] ) ? $field['choices'] : null,
                    'input_attrs' => !empty( $field['input_attrs'] ) ? $field['input_attrs'] : null,
                ) );
            }
        }
    }
}

/*if( class_exists( 'WP_Customize_Control' ) ) {
    class WP_Customize_Headline_Control extends WP_Customize_Control {
        public $type = 'headline';

        public function render_content() {
            echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
        }
    }
}*/