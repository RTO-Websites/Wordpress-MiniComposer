<?php

/**
 * @since 1.0.0
 * @author shennemann
 * @licence MIT
 */
class MinicomposerPublicBase {

    protected $columnCount = 0;

    protected $columnStyle = '';

    protected $options;


    public function __construct() {
        $this->addPxToGlobalOptions();
    }

    /**
     * Add pixel to numeric values
     */
    public function addPxToGlobalOptions() {
        if ( isset( $this->options['globalGutter'] ) ) {
            $this->options['globalGutter'] = $this->addPxToValue( $this->options['globalGutter'] );
        }
        if ( isset( $this->options['globalPadding'] ) ) {
            $this->options['globalPadding'] = $this->addPxToValue( $this->options['globalPadding'] );
        }
        if ( isset( $this->options['globalMinHeight'] ) ) {
            $this->options['globalMinHeight'] = $this->addPxToValue( $this->options['globalMinHeight'] );
        }
        if ( isset( $this->options['globalColumnMargin'] ) ) {
            $this->options['globalColumnMargin'] = $this->addPxToValue( $this->options['globalColumnMargin'] );
        }
        if ( isset( $this->options['globalRowMargin'] ) ) {
            $this->options['globalRowMargin'] = $this->addPxToValue( $this->options['globalRowMargin'] );
        }
    }

    /**
     * Add px to numeric value
     *
     * @param $value
     * @return string
     */
    public function addPxToValue( $value ) {
        if ( is_numeric( $value ) ) {
            $value .= 'px';
        } else {
            $split = explode( ' ', $value );
            if ( count( $split ) > 1 ) {
                foreach ( $split as $key => $part ) {
                    if ( is_numeric( $part ) ) {
                        $split[$key] = $part . 'px';
                    }
                }

                $value = implode( ' ', $split );
            }
        }
        return $value;
    }


    /**
     * Create HTML for rows and columns (recursive)
     *
     * @param $rows
     * @return string
     */
    protected function createRows( $rows ) {
        $gridOutput = '';

        // loop row
        foreach ( $rows as $rowIndex => $row ) {
            $rowOptions = isset( $row->options ) ? $row->options : array();
            $columns = !empty( $row->columns ) ? $row->columns : $row;

            $rowAttributes = '';
            foreach ( $rowOptions as $key => $value ) {
                $rowAttributes .= ' data-' . $key . '="' . $value . '"';
            }

            $rowStyle = $this->createColumnRowStyle( $rowOptions );
            $bgStyle = $this->createColumnRowBgStyle( $rowOptions );
            $rowClass = !empty( $rowOptions->cssclass ) ? $rowOptions->cssclass : '';
            $rowTag = !empty( $rowOptions->htmltag ) ? $rowOptions->htmltag : 'div';

            $gridOutput .= '<' . $rowTag . ' class="row  mc-row ' . $rowClass . '" style="' . $rowStyle . $bgStyle . '">';

            // loop columns
            foreach ( $columns as $columnIndex => $column ) {
                $colTag = !empty( $column->htmltag ) ? $column->htmltag : 'div';
                $this->columnCount += 1;
                // set classes for grid
                $columnClasses = $this->createColumnClasses( $column );
                $columnInnerStyle = $this->createColumnRowStyle( $column );
                $bgStyle = $this->createColumnRowBgStyle( $column );

                $columnStyle = '';

                // add column-specific gutter
                if ( isset( $column->gutter ) && $column->gutter !== '' ) {
                    $columnStyle .= 'padding-left:' . $this->addPxToValue( $column->gutter ) . ';'
                        . 'padding-right:' . $this->addPxToValue( $column->gutter )
                        . ';';
                }

                $customAttributes = '';
                if ( !empty( $column->customattributes ) ) {
                    $customAttributes = implode( ' ', explode( "\n", $column->customattributes ) );
                }
                if ( method_exists( $this, 'addDataAttributes' ) ) {
                    $customAttributes .= $this->addDataAttributes( $this->columnCount - 1 );
                }

                // generate html for column
                $columnOutput = '';
                $columnOutput .= ' <' . $colTag . ' class="mc-column-' . ( $this->columnCount ) . ' mc-column  columns '
                    . $columnClasses . '" style="' . $columnStyle . '" ' . $customAttributes
                    . ' data-columnkey="' . ( $this->columnCount - 1 ) . '">';
                $columnOutput .= '<div class="inner-column" style="' . $columnInnerStyle . '">';
                $columnOutput .= '<div class="column-content">';

                if ( function_exists( 'apply_filters' ) ) {
                    $columnOutput .= apply_filters( 'miniComposerAddColumnContent', '', $column );
                }

                // remove <p>
                $column->content = str_replace( '</p>', '<br /><br />', $column->content );
                $column->content = str_replace( '<p>', '', $column->content );
                // replace &nbsp;
                $column->content = str_replace( ' &nbsp;', ' ', $column->content );
                $columnOutput .= trim( $column->content );


                // column has inner-row -> call recursive createRows
                if ( !empty( $column->rows ) ) {
                    $columnOutput .= $this->createRows( $column->rows );
                }

                $columnOutput .= ' </div > ';

                // add column-background
                if ( !empty( $bgStyle ) ) {
                    $columnOutput .= '<div class="mc-background" style="' . $bgStyle . '"></div>';
                }

                $columnOutput .= '</div>';
                $columnOutput .= '</' . $colTag . '>';

                if ( method_exists( $this, 'filterColumn' ) ) {
                    $columnOutput = $this->filterColumn( $columnOutput );
                }
                if ( function_exists( 'apply_filters' ) ) {
                    $columnOutput = apply_filters( 'miniComposerFilterColumn', $columnOutput );
                }

                $gridOutput .= $columnOutput;
            }
            $gridOutput .= ' </' . $rowTag . '>';
        }

        return $gridOutput;
    }

    /**
     * Adds global style for grid on header
     */
    public function addHeaderStyle() {
        echo '<style class="mc-style">';
        // global style
        echo '.row .inner-column {
                ';
        echo 'position:relative;z-index:50;';
        if ( isset( $this->options['globalPadding'] ) ) {
            echo 'padding:' . $this->options['globalPadding'] . ';';
        }
        echo isset( $this->options['globalMinHeight'] ) ? 'min-height:' . $this->options['globalMinHeight'] . ';' : '';
        echo isset( $this->options['globalColumnMargin'] ) ? 'margin-bottom:' . $this->options['globalColumnMargin'] . ';' : '';
        echo '}';

        if ( isset( $this->options['globalRowMargin'] ) && $this->options['globalRowMargin'] !== '' ) {
            echo '.mc-row{
                margin-bottom:' . $this->options['globalRowMargin'] . ';}';
        }

        if ( isset( $this->options['globalGutter'] ) && $this->options['globalGutter'] !== '' ) {
            echo '.mc-column{
                padding-left:' . $this->options['globalGutter'] . ';' . ';padding-right:' . $this->options['globalGutter'] . ';}';
        }

        echo '.mc-column .clear-left {';
        echo 'clear: left;';
        echo '}';

        echo '.mc-column .mc-background { ';
        echo 'position:absolute;top:0;left:0;bottom:0;right:0;z-index:0;transform:translateZ( 0 );';
        echo '}';
        echo '.mc-column .column-content {';
        echo 'position: relative; z-index: 50;';
        echo '}';

        // column style
        echo $this->columnStyle;
        echo ' </style>';
    }

    /**
     * Create classes like small-4 or large-5 for grid
     *
     * @param $column
     * @return string
     */
    public function createColumnClasses( $column ) {
        $columnClasses = '';
        if ( empty( $this->options['useBootstrap'] ) ) {
            $columnClasses .= !empty( $column->small ) ? ' small-' . $column->small : '';
            $columnClasses .= !empty( $column->medium ) ? ' medium-' . $column->medium : '';
            $columnClasses .= !empty( $column->large ) ? ' large-' . $column->large : '';
        } else {
            $columnClasses .= !empty( $column->small ) ? ' col-xs-' . $column->small : '';
            $columnClasses .= !empty( $column->medium ) ? ' col-md-' . $column->medium : '';
            $columnClasses .= !empty( $column->large ) ? ' col-lg-' . $column->large : '';
        }

        if ( !empty( $column->cssclass ) ) {
            $columnClasses .= ' ' . $column->cssclass;
        }

        return $columnClasses;
    }


    /**
     * Create background-style for column
     */
    public function createColumnRowBgStyle( $element ) {
        $style = '';
        $style .= !empty( $element->backgroundimage ) ? 'background-image:url( ' . $element->backgroundimage . ' );' : '';
        $style .= !empty( $element->backgroundcolor ) ? 'background-color:' . $element->backgroundcolor . ';' : '';
        $style .= !empty( $element->backgroundposition ) ? 'background-position:' . $element->backgroundposition . ';' : '';
        $style .= !empty( $element->backgroundrepeat ) ? 'background-repeat:' . $element->backgroundrepeat . ';' : '';
        $style .= !empty( $element->backgroundsize ) ? 'background-size:' . $element->backgroundsize . ';' : '';

        return $style;
    }

    /**
     * Create style for column (background, padding)
     */
    public function createColumnRowStyle( $element ) {
        $style = '';

        if ( isset( $element->padding ) && $element->padding !== '' ) {
            $style .= 'padding:' . $this->addPxToValue( $element->padding ) . ';';
        }

        $style .= isset( $element->minheight ) && $element->minheight !== ''
            ? 'min-height:' . $this->addPxToValue( $element->minheight ) . ';' : '';

        return $style;
    }
}