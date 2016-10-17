<?php

class UnitException extends Exception {}

abstract class Unit {
    protected $depth=0;
    function getComposite() {
        return null;
    }

    // mz: added
    function accept( ArmyVisitor $visitor ) {
        $method = "visit".get_class( $this );
        $visitor->$method( $this );
    }
    protected function setDepth( $depth ) {
        $this->depth=$depth;
    }

    function getDepth() {
        return $this->depth;
    }
    // mz: end added
    abstract function bombardStrength();
}


abstract class CompositeUnit extends Unit {
    private $units = array();

    function getComposite() {
        return $this;
    }

    protected function units() {
        return $this->units;
    }

    function removeUnit( Unit $unit ) {
        // >= php 5.3
        //$this->units = array_udiff( $this->units, array( $unit ), 
        //                function( $a, $b ) { return ($a === $b)?0:1; } );

        // < php 5.3
        $this->units = array_udiff( $this->units, array( $unit ), 
                        create_function( '$a,$b', 'return ($a === $b)?0:1;' ) );
    }
// mz: change
// the old method 
/*
    function addUnit( Unit $unit ) {
        if ( in_array( $unit, $this->units, true ) ) {
            return;
        }
        $this->units[] = $unit;
    }
*/
// the new method 
    function addUnit( Unit $unit ) {
        foreach ( $this->units as $thisunit ) {
            if ( $unit === $thisunit ) {
                return;
            }
        }
        $unit->setDepth($this->depth+1);
        $this->units[] = $unit;
    }
// mz: end change

// mz: add
    function accept( ArmyVisitor $visitor ) {
        $method = "visit".get_class( $this );
        $visitor->$method( $this );
        foreach ( $this->units as $thisunit ) {
            $thisunit->accept( $visitor );
        }
    }
// mz: end add
}
class Army extends CompositeUnit {

    function bombardStrength() {
        $ret = 0;
        foreach( $this->units() as $unit ) {
            $ret += $unit->bombardStrength();
        }
        return $ret;
    }

}

class Archer extends Unit {
    function bombardStrength() {
        return 4;
    }
}

class LaserCannonUnit extends Unit {
    function bombardStrength() {
        return 44;
    }
}

class Cavalry extends Unit {
    function bombardStrength() {
        return 33;
    }
}

class TroopCarrier {

    function addUnit( Unit $unit ) {
        if ( $unit instanceof Cavalry ) {
            throw new UnitException("Can't get a horse on the vehicle");
        }
        super::addUnit( $unit );
    }

    function bombardStrength() {
        return 0;
    }
}

// mz: new classes /////////////////////////
abstract class ArmyVisitor  {
    abstract function visit( Unit $node );

    function visitArcher( Archer $node ) {
        $this->visit( $node );
    }

    function visitCavalry( Cavalry $node ) {
        $this->visit( $node );
    }

    function visitLaserCannonUnit( LaserCannonUnit $node ) {
        $this->visit( $node );
    }

    function visitTroopCarrierUnit( TroopCarrierUnit $node ) {
        $this->visit( $node );
    }

    function visitArmy( Army $node ) {
        $this->visit( $node );
    }
}

class TextDumpArmyVisitor extends ArmyVisitor {
    private $text="";

    function visit( Unit $node ) {
        $txt = "";
        $pad = 4*$node->getDepth();
        $txt .= sprintf( "%{$pad}s", "" );
        $txt .= get_class($node).": ";
        $txt .= "bombard: ".$node->bombardStrength()."\n";
        $this->text .= $txt;
    }

    function getText() {
        return $this->text;
    }
}

class TaxCollectionVisitor extends ArmyVisitor {
    private $due=0;
    private $report="";

    function visit( Unit $node ) {
        $this->levy( $node, 1 );
    }

    function visitArcher( Archer $node ) {
        $this->levy( $node, 2 );
    }

    function visitCavalry( Cavalry $node ) {
        $this->levy( $node, 3 );
    }

    function visitTroopCarrierUnit( TroopCarrierUnit $node ) {
        $this->levy( $node, 5 );
    }

    private function levy( Unit $unit, $amount ) {
        $this->report .= "Tax levied for ".get_class( $unit );
        $this->report .= ": $amount\n";
        $this->due += $amount;
    }

    function getReport() {
        return $this->report;
    }

    function getTax() {
        return $this->due;
    }
}
// mz: end new classes /////////////////////////

// mz: delete client code

//$tc= new TroopCarrier();
//$ca= new Cavalry();

//$tc->addUnit( $ca ) ;
// end delete

// mz: end delete client code

// mz: add client code
$main_army = new Army();
$main_army->addUnit( new Archer() );
$main_army->addUnit( new LaserCannonUnit() );
$main_army->addUnit( new Cavalry() );
$textdump = new TextDumpArmyVisitor();
$main_army->accept( $textdump  );

print $textdump->getText();


$main_army = new Army();
$main_army->addUnit( new Archer() );
$main_army->addUnit( new LaserCannonUnit() );
$main_army->addUnit( new Cavalry() );

$taxcollector = new TaxCollectionVisitor();
$main_army->accept( $taxcollector );
print $taxcollector->getReport();
print "TOTAL: ";
print $taxcollector->getTax()."\n";

// mz: end add client code
?>
