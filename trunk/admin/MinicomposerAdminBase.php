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

    protected $oneToTwelve = array( '', 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 'hide' );

    protected $responsiveFields = array(
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
        'responsiveClass' => array(
            'type' => 'text',
            'label' => 'CSS-Class',
        ),
        'htmltag' => array(
            'type' => 'text',
            'label' => 'HTML-Tag',
            'list' => 'datalist-htmltags',
        ),
        'columnPadding' => array(
            'type' => 'text',
            'label' => 'Padding',
        ),
        'columnGutter' => array(
            'type' => 'text',
            'label' => 'Gutter',
            'trClass' => 'hide-on-row',
        ),
        'static' => array(
            'type' => 'checkbox',
            'label' => 'Position static',
        ),
        'columnBackground' => array(
            'type' => 'background',
            'label' => 'Background',
        ),
        'fullwidthbg' => array(
            'type' => 'checkbox',
            'label' => 'Full width background',
            'trClass' => 'hide-on-column',
        ),
        'minHeight' => array(
            'type' => 'input',
            'label' => 'min-height',
        ),
        'customAttributes' => array(
            'type' => 'textarea',
            'label' => 'Custom-Attributes',
        ),
    );

    private $columnKey = 0;

    public function __construct() {
        $this->addOptionsToResponsiveFields();
    }


    /**
     * Returns content without divs of columns (recursive)
     */
    public function getColumnContent( $rows ) {
        $output = '';

        if ( empty( $rows ) )
            return $output;

        // loop row
        foreach ( $rows as $rowIndex => $row ) {
            $columns = isset( $row->columns ) ? $row->columns : $row;
            // loop columns
            foreach ( $columns as $columnIndex => $column ) {
                //$column->content = str_replace( '</p>', '<br /><br />', $column->content );
                //$column->content = str_replace( '<p>', '', $column->content );

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
            <?php
            $rowOptions = isset( $row->options ) ? $row->options : array();
            $columns = !empty( $row->columns ) ? $row->columns : $row;

            $rowAttributes = '';
            foreach ( $rowOptions as $key => $value ) {
                $rowAttributes .= ' data-' . $key . '="' . $value . '"';
            }
            ?>
            <div class="minicomposer-row" draggable="true" <?php echo $rowAttributes; ?>>
                <?php foreach ( $columns as $col ): ?>
                    <div class="minicomposer-column" draggable="true"
                        <?php
                        echo ' data-columnkey="' . $this->columnKey . '"';
                        $this->columnKey += 1;

                        foreach ( $col as $key => $value ) {
                            if ( $key == 'content' )
                                continue;

                            if ( is_array( $value ) || is_object( $value ) ) {
                                //echo ' data-' . $key . '="' . json_encode($value) . '" ';
                            } elseif ( !empty( $value ) ) {
                                echo ' data-' . $key . '="' . $value . '" ';
                            } else {
                                continue;
                            }
                        }
                        ?>
                    >
                        <span class="content"><?php echo( !empty( $col->content ) ? $col->content : '' ); ?></span>
                        <span class="column-bg"></span>
                        <span class="column-count"><?php echo( !empty( $col->medium ) ? $col->medium : '12' ); ?></span>
                        <span class="column-class"><?php echo( !empty( $col->cssclass ) ? $col->cssclass : '' ); ?></span>
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

    /**
     * Returns content of all columns in a list
     */
    public static function getColumnContentList( $rows, $returnArray = array() ) {
        foreach ( $rows as $row ) {
            $columns = !empty( $row->columns ) ? $row->columns : $row;
            foreach ( $columns as $col ) {
                $returnArray[] = !empty( $col->content ) ? $col->content : '';

                if ( !empty( $col->rows ) ) {
                    $returnArray = MinicomposerAdminBase::getColumnContentList( $col->rows, $returnArray );
                }
            }
        }

        return $returnArray;
    }

    /**
     * Change content of a spezific column
     */
    public static function changeColumnContent( $rows, $columnId, $newContent, &$count = 0 ) {
        foreach ( $rows as $rowKey => $row ) {
            $columns = !empty( $row->columns ) ? $row->columns : $row;
            foreach ( $columns as $colKey => $col ) {
                if ( $count == $columnId ) {
                    // column match -> change content
                    if ( is_object( $rows[$rowKey] ) ) {
                        $rows[$rowKey]->columns[$colKey]->content = $newContent;
                    } else {
                        $rows[$rowKey][$colKey]->content = $newContent;
                    }
                }
                $count += 1;

                // has sub rows -> call recursive
                if ( !empty( $col->rows ) ) {
                    MinicomposerAdminBase::changeColumnContent( $col->rows, $columnId, $newContent, $count );
                }
            }
        }

        return $rows;
    }
}