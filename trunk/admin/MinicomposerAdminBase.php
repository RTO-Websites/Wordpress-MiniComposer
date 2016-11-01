<?php

/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */
class MinicomposerAdminBase {
    protected $optionFields = array(
        'minicomposerColumns' => array(
            'type' => 'textarea',
            'label' => '',
            'trClass' => 'hidden',
            'isJson' => true,
        ),
    );

    protected $options;

    protected $oneToTwelve = array( '', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12 );

    protected $responsiveFields = array(
        'responsiveClass' => array(
            'type' => 'text',
            'label' => 'CSS-Class',
        ),
        'customAttributes' => array(
            'type' => 'textarea',
            'label' => 'Custom-Attributes',
        ),
        'responsiveSmall' => array(
            'type' => 'select',
            'label' => 'Small',
        ),
        'responsiveMedium' => array(
            'type' => 'select',
            'label' => 'Medium',
        ),
        'responsiveLarge' => array(
            'type' => 'select',
            'label' => 'Large',
        ),
    );

    protected $styleFields = array(
        'columnPadding' => array(
            'type' => 'text',
            'label' => 'Padding',
        ),
        'columnGutter' => array(
            'type' => 'text',
            'label' => 'Gutter',
        ),
        'columnBackground' => array(
            'type' => 'background',
            'label' => 'Background',
        ),
        'minHeight' => array(
            'type' => 'input',
            'label' => 'min-height',
        ),
    );

    public function __construct() {
        $this->addOptionsToResponsiveFields();
    }


    /**
     * Returns content of columns (recursive)
     */
    public function getColumnContent( $rows ) {
        $output = '';

        if ( empty( $rows ) ) return $output;

        // loop row
        foreach ( $rows as $rowIndex => $row ) {
            // loop columns
            foreach ( $row as $columnIndex => $column ) {
                $column->content = str_replace( '</p>', '<br /><br />', $column->content );
                $column->content = str_replace( '<p>', '', $column->content );

                // remove shortcodes
                $column->content = preg_replace( "/\[[^\]]*\]/", "", $column->content );

                $output .= trim( $column->content );

                // column has inner-row -> call recursive getColumnContent
                if ( !empty( $column->rows ) ) {
                    $output .= $this->getColumnContent( $column->rows );
                }
            }
        }

        return $output;
    }


    /**
     * Extract rows from json-string, or return empty row with empty col
     *
     * @param $composerRows
     * @param string $emptyContent
     * @return array|mixed|object
     */
    public function extractRows( $composerRows, $emptyContent = '' ) {
        if ( empty( $composerRows ) ) {
            $composerRows = array(
                array(
                    array(
                        'medium' => 12,
                        'content' => $emptyContent,
                    ),
                ),
            );
            $composerRows = json_decode( json_encode( $composerRows ) );
        } else if ( is_string( $composerRows ) ) {
            $composerRows = json_decode( $composerRows );
        }

        return $composerRows;
    }


    /**
     * Output rows
     *
     * @param $rows
     */
    public function getRows( $rows ) {
        foreach ( $rows as $row ): ?>
            <div class="minicomposer-row" draggable="true">
                <?php foreach ( $row as $col ):?>
                    <div class="minicomposer-column" draggable="true"
                        <?php
                        foreach ( $col as $key => $value ) {
                            if ( $key == 'content' )
                                continue;

                            if ( is_array( $value ) || is_object( $value ) ) {
                                //echo ' data-' . $key . '="' . json_encode($value) . '" ';
                            } else {
                                echo ' data-' . $key . '="' . $value . '" ';
                            }
                        }
                        ?>
                    >
                        <span class="content"><?php echo( !empty( $col->content ) ? $col->content : '' ); ?></span>
                        <span class="column-bg"></span>
                        <span class="column-count"><?php echo $col->medium; ?></span>
                        <?php
                        if ( !empty( $col->rows ) ) {
                            $this->getRows( $col->rows );
                        }
                        ?>
                    </div>
                <?php endforeach; ?>

                <span class="minicomposer-delete"></span>
                <span class="row-bg"></span>
            </div>
        <?php endforeach;
    }


    public function addOptionsToResponsiveFields() {
        foreach ( $this->responsiveFields as $key => $value ) {
            if ( $value['type'] == 'select' ) {
                $this->responsiveFields[$key]['options'] = $this->oneToTwelve;
            }
        }
    }
}