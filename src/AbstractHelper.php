<?php

namespace IMI\DatabaseHelper;

class AbstractHelper {
    protected $output;
    protected $asker;

    /**
     * Provide an output facility or null for no output
     * see writeln implementation for details
     *
     * @param $output mixed
     */
    public function __construct( $output = null, $asker = null) {
        $this->output = $output;
        $this->asker = $asker;
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
            call_user_func_array($this->output, [ $text ]);
        } else if ( is_object( $this->output ) && method_exists( $this->output, 'writeln' ) ) {
            $this->output->writeln( $text );
        }

    }

    protected function ask( $prompt, $default ) {
        if ( is_callable( $this->asker ) ) {
            $result = call_user_func_array($this->asker, [$prompt, $default] );
            return $result;
        } else if ( is_object( $this->asker ) && method_exists( $this->asker, 'ask' ) ) {
            $result = $this->asker->ask( $prompt , $default );
            return $result;
        }  else if (function_exists('readline')) {
            $result = readline ( $prompt );
            if (!$result) {
                $result = $default;
            }
            return $result;
        } else {
            echo $prompt;
            $result = stream_get_line(STDIN, 1024, PHP_EOL);
            if (!$result) {
                $result = $default;
            }
            return $result;
        }
    }
}
