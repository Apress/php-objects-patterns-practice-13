<?php

class UnitException extends Exception {}

abstract class Unit {
    function getComposite() {
        return null;
    }

    abstract function bombardStrength();

    function textDump( $num=0 ) {
        $txtout = "";
        $pad = 4*$num;
        $txtout .= sprintf( "%{$pad}s", "" );
        $txtout .= get_class($this).": ";
        $txtout .= "bombard: ".$this->bombardStrength()."\n";
        return $txtout;
    }

    function unitCount() {
        return 1;
    }
}

abstract class CompositeUnit extends Unit {
    private $units = array();

    function getComposite() {
        return $this;
    }

    function units() {
        return $this->units;
    }

    function removeUnit( Unit $unit ) {
        $units = array();
        foreach ( $this->units as $thisunit ) {
            if ( $unit !== $thisunit ) {
                $units[] = $thisunit;
            }
        }
        $this->units = $units;
    }

    function addUnit( Unit $unit ) {
        if ( in_array( $unit, $this->units, true ) ) {
            return;
        }
        $this->units[] = $unit;
    }

    function unitCount() {
        $count = 0;
        foreach ( $this->units as $unit ) {
            $count += $unit->unitCount(); 
        }
        return $count;
    }

    function textDump( $num=0 ) {
        $txtout = parent::textDump( $num );
        foreach ( $this->units as $unit ) {
            $txtout .= $unit->textDump( $num + 1 ); 
        }
        return $txtout;
    }

}


class Archer extends Unit {
    function bombardStrength() {
        return 4;
    }
    function unitCount() {
        return 1;
    }
}

class Cavalry extends Unit {
    function bombardStrength() {
        return 2;
    }
}

class LaserCanonUnit extends Unit {
    function bombardStrength() {
        return 44;
    }
}

class TroopCarrier extends CompositeUnit {

    function addUnit( Unit $unit ) {
        if ( $unit instanceof Cavalry ) {
            throw new UnitException("Can't get a horse on the vehicle");
        }
        parent::addUnit( $unit );
    }

    function bombardStrength() {
        return 0;
    }
}

// end previous code

class Army extends CompositeUnit {

    function bombardStrength() {
        $strength = 0;
        foreach( $this->units() as $unit ) {
            $strength += $unit->bombardStrength();
        }
        return $strength;
    }
}

$main_army = new Army();
$main_army->addUnit( new Archer() );
$main_army->addUnit( new LaserCanonUnit() );
$sub_army=new Army();
$sub_army->addUnit( new Cavalry() );
$main_army->addUnit( $sub_army );
$main_army->addUnit( new Cavalry() );
print $main_army->textDump();
?>
