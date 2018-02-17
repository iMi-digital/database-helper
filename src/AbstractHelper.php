<?php

namespace IMI\DatabaseHelper;

class AbstractHelper {
    protected $output;

    /**
     * Provide an output facility or null for no output
     * see writeln implementation for details
     *
     * @param $output mixed
     */
    public function __construct( $output = null ) {
        $this->output = $output;
    }

    /**
     * Output text to the screen
     * For callbacks or classes that implement a writeln method
     * (having symfony style outputs in mind)
     *
     * @param $text
     */
    protected function writeln( $text ) {
        if ( is_callable( $this->output ) ) {
            $this->output( $text );
        } else if ( is_object( $this->output ) && method_exists( $this->output, 'writeln' ) ) {
            $this->output->writeln( $text );
        }
    }


}
