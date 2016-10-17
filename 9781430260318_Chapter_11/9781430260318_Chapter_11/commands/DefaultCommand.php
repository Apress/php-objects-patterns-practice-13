<?php
require_once( "Command.php" );

class DefaultCommand extends Command {

    function execute( CommandContext $context ) {
        // default command
        return true;
    }
}
?>
