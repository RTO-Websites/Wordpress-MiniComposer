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
            $gridOutput .= '<div class="row  mc-row">';

            // loop columns
            foreach ( $row as $columnIndex => $column ) {
                $this->columnCount += 1;
                // set classes for grid
                $columnClasses = $this->createColumnClasses( $column );
                $columnInnerStyle = $this->createColumnStyle( $column );

                $columnStyle = '';

                // add column-specific gutter
                if ( isset( $column->gutter ) && $column->gutter !== '' ) {
                    $columnStyle .= 'padding-left:' . $this->addPxToValue( $column->gutter ) . ';'
                        . 'padding-right:' . $this->addPxToValue( $column->gutter )
                        . ';';
                }


                // generate html for column
                $gridOutput .= '<div class="mc-column-' . $this->columnCount . ' mc-column  columns ' . $columnClasses . '" style="' . $columnStyle . '">';
                $gridOutput .= '<div class="inner-column" style="' . $columnInnerStyle . '">';
                // remove <p>
                $column->content = str_replace( '</p>', '<br /><br />', $column->content );
                $column->content = str_replace( '<p>', '', $column->content );
                // replace &nbsp;
                $column->content = str_replace( '&nbsp;', ' ', $column->content );
                $gridOutput .= trim( $column->content );

                // column has inner-row -> call recursive createRows
                if ( !empty( $column->rows ) ) {
                    $gridOutput .= $this->createRows( $column->rows );
                }

                $gridOutput .= '</div>';
                $gridOutput .= '</div>';
            }
            $gridOutput .= '</div>';
        }

        return $gridOutput;
    }

    /**
     * Adds global style for grid on header
     */
    public function addHeaderStyle() {
        echo '<style class="mc-style">';
        // global style
        echo '.row .inner-column{';
        echo 'position:relative;';
        if ( isset( $this->options['globalPadding'] ) ) {
            echo 'padding:' . $this->options['globalPadding'] . ';';
        }
        echo isset( $this->options['globalMinHeight'] ) ? 'min-height:' . $this->options['globalMinHeight'] . ';' : '';
        echo isset( $this->options['globalColumnMargin'] ) ? 'margin-bottom:' . $this->options['globalColumnMargin'] . ';' : '';
        echo '}';

        if ( isset( $this->options['globalRowMargin'] ) && $this->options['globalRowMargin'] !== '' ) {
            echo '.mc-row{margin-bottom:' . $this->options['globalRowMargin'] . ';}';
        }

        if ( isset( $this->options['globalGutter'] ) && $this->options['globalGutter'] !== '' ) {
            echo '.mc-column{padding-left:' . $this->options['globalGutter'] . ';' . ';padding-right:' . $this->options['globalGutter'] . ';}';
        }

        echo '.mc-column.clear-left{';
        echo 'clear: left;';
        echo '}';

        // column style
        echo $this->columnStyle;
        echo '</style>';
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
     * Create style for column (background, padding)
     */
    public function createColumnStyle( $column ) {
        $columnStyle = '';
        $columnStyle .= !empty( $column->backgroundimage ) ? 'background-image:url(' . $column->backgroundimage . ');' : '';
        $columnStyle .= !empty( $column->backgroundcolor ) ? 'background-color:' . $column->backgroundcolor . ';' : '';
        $columnStyle .= !empty( $column->backgroundposition ) ? 'background-position:' . $column->backgroundposition . ';' : '';
        $columnStyle .= !empty( $column->backgroundrepeat ) ? 'background-repeat:' . $column->backgroundrepeat . ';' : '';
        $columnStyle .= !empty( $column->backgroundsize ) ? 'background-size:' . $column->backgroundsize . ';' : '';

        if ( isset( $column->padding ) && $column->padding !== '' ) {
            $columnStyle .= 'padding:' . $this->addPxToValue( $column->padding ) . ';';
        }

        $columnStyle .= isset( $column->minheight ) && $column->minheight !== '' ? 'min-height:' . $this->addPxToValue( $column->minheight ) . ';' : '';

        return $columnStyle;
    }
}