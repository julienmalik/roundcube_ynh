<?php

namespace Sabre\VObject;

use
    Sabre\VObject\Component\VCalendar,
    Sabre\VObject\Component\VCard;

class ComponentTest extends \PHPUnit_Framework_TestCase {

    function testIterate() {

        $comp = new VCalendar(array(), false);

        $sub = $comp->createComponent('VEVENT');
        $comp->add($sub);

        $sub = $comp->createComponent('VTODO');
        $comp->add($sub);

        $count = 0;
        foreach($comp->children() as $key=>$subcomponent) {

           $count++;
           $this->assertInstanceOf('Sabre\\VObject\\Component',$subcomponent);

        }
        $this->assertEquals(2,$count);
        $this->assertEquals(1,$key);

    }

    function testMagicGet() {

        $comp = new VCalendar(array(), false);

        $sub = $comp->createComponent('VEVENT');
        $comp->add($sub);

        $sub = $comp->createComponent('VTODO');
        $comp->add($sub);

        $event = $comp->vevent;
        $this->assertInstanceOf('Sabre\\VObject\\Component', $event);
        $this->assertEquals('VEVENT', $event->name);

        $this->assertInternalType('null', $comp->vjournal);

    }

    function testMagicGetGroups() {

        $comp = new VCard();

        $sub = $comp->createProperty('GROUP1.EMAIL','1@1.com');
        $comp->add($sub);

        $sub = $comp->createProperty('GROUP2.EMAIL','2@2.com');
        $comp->add($sub);

        $sub = $comp->createProperty('EMAIL','3@3.com');
        $comp->add($sub);

        $emails = $comp->email;
        $this->assertEquals(3, count($emails));

        $email1 = $comp->{"group1.email"};
        $this->assertEquals('EMAIL', $email1[0]->name);
        $this->assertEquals('GROUP1', $email1[0]->group);

        $email3 = $comp->{".email"};
        $this->assertEquals('EMAIL', $email3[0]->name);
        $this->assertEquals(null, $email3[0]->group);

    }

    function testMagicIsset() {

        $comp = new VCalendar();

        $sub = $comp->createComponent('VEVENT');
        $comp->add($sub);

        $sub = $comp->createComponent('VTODO');
        $comp->add($sub);

        $this->assertTrue(isset($comp->vevent));
        $this->assertTrue(isset($comp->vtodo));
        $this->assertFalse(isset($comp->vjournal));

    }

    function testMagicSetScalar() {

        $comp = new VCalendar();
        $comp->myProp = 'myValue';

        $this->assertInstanceOf('Sabre\\VObject\\Property',$comp->MYPROP);
        $this->assertEquals('myValue',(string)$comp->MYPROP);


    }

    function testMagicSetScalarTwice() {

        $comp = new VCalendar(array(), false);
        $comp->myProp = 'myValue';
        $comp->myProp = 'myValue';

        $this->assertEquals(1,count($comp->children()));
        $this->assertInstanceOf('Sabre\\VObject\\Property',$comp->MYPROP);
        $this->assertEquals('myValue',(string)$comp->MYPROP);

    }

    function testMagicSetArray() {

        $comp = new VCalendar();
        $comp->ORG = array('Acme Inc', 'Section 9');

        $this->assertInstanceOf('Sabre\\VObject\\Property',$comp->ORG);
        $this->assertEquals(array('Acme Inc', 'Section 9'),$comp->ORG->getParts());

    }

    function testMagicSetComponent() {

        $comp = new VCalendar();

        // Note that 'myProp' is ignored here.
        $comp->myProp = $comp->createComponent('VEVENT');

        $this->assertEquals(1, count($comp));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testMagicSetTwice() {

        $comp = new VCalendar(array(), false);

        $comp->VEVENT = $comp->createComponent('VEVENT');
        $comp->VEVENT = $comp->createComponent('VEVENT');

        $this->assertEquals(1, count($comp->children()));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testArrayAccessGet() {

        $comp = new VCalendar(array(), false);

        $event = $comp->createComponent('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertEquals(2,count($comp->children()));
        $this->assertTrue($comp->vevent[1] instanceof Component);
        $this->assertEquals('Event 2', (string)$comp->vevent[1]->summary);

    }

    function testArrayAccessExists() {

        $comp = new VCalendar();

        $event = $comp->createComponent('VEVENT');
        $event->summary = 'Event 1';

        $comp->add($event);

        $event2 = clone $event;
        $event2->summary = 'Event 2';

        $comp->add($event2);

        $this->assertTrue(isset($comp->vevent[0]));
        $this->assertTrue(isset($comp->vevent[1]));

    }

    /**
     * @expectedException LogicException
     */
    function testArrayAccessSet() {

        $comp = new VCalendar();
        $comp['hey'] = 'hi there';

    }
    /**
     * @expectedException LogicException
     */
    function testArrayAccessUnset() {

        $comp = new VCalendar();
        unset($comp[0]);

    }

    function testAddScalar() {

        $comp = new VCalendar(array(), false);

        $comp->add('myprop','value');

        $this->assertEquals(1, count($comp->children()));

        $bla = $comp->children[0];

        $this->assertTrue($bla instanceof Property);
        $this->assertEquals('MYPROP',$bla->name);
        $this->assertEquals('value',(string)$bla);

    }

    function testAddScalarParams() {

        $comp = new VCalendar(array(), false);

        $comp->add('myprop','value',array('param1'=>'value1'));

        $this->assertEquals(1, count($comp->children()));

        $bla = $comp->children[0];

        $this->assertInstanceOf('Sabre\\VObject\\Property', $bla);
        $this->assertEquals('MYPROP',$bla->name);
        $this->assertEquals('value', (string)$bla);

        $this->assertEquals(1, count($bla->parameters()));

        $this->assertEquals('PARAM1',$bla->parameters['PARAM1']->name);
        $this->assertEquals('value1',$bla->parameters['PARAM1']->getValue());

    }


    function testAddComponent() {

        $comp = new VCalendar(array(), false);

        $comp->add($comp->createComponent('VEVENT'));

        $this->assertEquals(1, count($comp->children()));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    function testAddComponentTwice() {

        $comp = new VCalendar(array(), false);

        $comp->add($comp->createComponent('VEVENT'));
        $comp->add($comp->createComponent('VEVENT'));

        $this->assertEquals(2, count($comp->children()));

        $this->assertEquals('VEVENT',$comp->VEVENT->name);

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAddArgFail() {

        $comp = new VCalendar();
        $comp->add($comp->createComponent('VEVENT'),'hello');

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testAddArgFail2() {

        $comp = new VCalendar();
        $comp->add(array());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testMagicSetInvalid() {

        $comp = new VCalendar(array(), false);

        // Note that 'myProp' is ignored here.
        $comp->myProp = new \StdClass();

    }

    function testMagicUnset() {

        $comp = new VCalendar(array(), false);
        $comp->add($comp->createComponent('VEVENT'));

        unset($comp->vevent);

        $this->assertEquals(0, count($comp->children()));

    }


    function testCount() {

        $comp = new VCalendar();
        $this->assertEquals(1,$comp->count());

    }

    function testChildren() {

        $comp = new VCalendar(array(), false);

        // Note that 'myProp' is ignored here.
        $comp->add($comp->createComponent('VEVENT'));
        $comp->add($comp->createComponent('VTODO'));

        $r = $comp->children();
        $this->assertInternalType('array', $r);
        $this->assertEquals(2,count($r));
    }

    function testGetComponents() {

        $comp = new VCalendar();

        $comp->add($comp->createProperty('FOO','BAR'));
        $comp->add($comp->createComponent('VTODO'));

        $r = $comp->getComponents();
        $this->assertInternalType('array', $r);
        $this->assertEquals(1, count($r));
        $this->assertEquals('VTODO', $r[0]->name);
    }

    function testSerialize() {

        $comp = new VCalendar(array(), false);
        $this->assertEquals("BEGIN:VCALENDAR\r\nEND:VCALENDAR\r\n", $comp->serialize());

    }

    function testSerializeChildren() {

        $comp = new VCalendar(array(), false);
        $comp->add($comp->createComponent('VEVENT'));
        $comp->add($comp->createComponent('VTODO'));

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nBEGIN:VTODO\r\nEND:VTODO\r\nEND:VCALENDAR\r\n", $str);

    }

    function testSerializeOrderCompAndProp() {

        $comp = new VCalendar(array(), false);
        $comp->add($comp->createComponent('VEVENT'));
        $comp->add('PROP1','BLABLA');
        $comp->add('VERSION','2.0');
        $comp->add($comp->createComponent('VTIMEZONE'));

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPROP1:BLABLA\r\nBEGIN:VTIMEZONE\r\nEND:VTIMEZONE\r\nBEGIN:VEVENT\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n", $str);

    }

    function testAnotherSerializeOrderProp() {

        $prop4s=array('1', '2', '3', '4', '5', '6', '7', '8', '9', '10');

        $comp = new VCard(array(), false);

        $comp->__set('SOMEPROP','FOO');
        $comp->__set('ANOTHERPROP','FOO');
        $comp->__set('THIRDPROP','FOO');
        foreach ($prop4s as $prop4) {
            $comp->add('PROP4', 'FOO '.$prop4);
        }
        $comp->__set('PROPNUMBERFIVE', 'FOO');
        $comp->__set('PROPNUMBERSIX', 'FOO');
        $comp->__set('PROPNUMBERSEVEN', 'FOO');
        $comp->__set('PROPNUMBEREIGHT', 'FOO');
        $comp->__set('PROPNUMBERNINE', 'FOO');
        $comp->__set('PROPNUMBERTEN', 'FOO');
        $comp->__set('VERSION','2.0');
        $comp->__set('UID', 'FOO');

        $str = $comp->serialize();

        $this->assertEquals("BEGIN:VCARD\r\nVERSION:2.0\r\nSOMEPROP:FOO\r\nANOTHERPROP:FOO\r\nTHIRDPROP:FOO\r\nPROP4:FOO 1\r\nPROP4:FOO 2\r\nPROP4:FOO 3\r\nPROP4:FOO 4\r\nPROP4:FOO 5\r\nPROP4:FOO 6\r\nPROP4:FOO 7\r\nPROP4:FOO 8\r\nPROP4:FOO 9\r\nPROP4:FOO 10\r\nPROPNUMBERFIVE:FOO\r\nPROPNUMBERSIX:FOO\r\nPROPNUMBERSEVEN:FOO\r\nPROPNUMBEREIGHT:FOO\r\nPROPNUMBERNINE:FOO\r\nPROPNUMBERTEN:FOO\r\nUID:FOO\r\nEND:VCARD\r\n", $str);

    }

    function testInstantiateWithChildren() {

        $comp = new VCard(array(
            'ORG' => array('Acme Inc.', 'Section 9'),
            'FN' => 'Finn The Human',
        ));

        $this->assertEquals(array('Acme Inc.', 'Section 9'), $comp->ORG->getParts());
        $this->assertEquals('Finn The Human', $comp->FN->getValue());

    }

    function testInstantiateSubComponent() {

        $comp = new VCalendar();
        $event = $comp->createComponent('VEVENT', array(
            $comp->createProperty('UID', '12345'),
        ));
        $comp->add($event);

        $this->assertEquals('12345', $comp->VEVENT->UID->getValue());

    }

    function testRemoveByName() {

        $comp = new VCalendar(array(), false);
        $comp->add('prop1','val1');
        $comp->add('prop2','val2');
        $comp->add('prop2','val2');

        $comp->remove('prop2');
        $this->assertFalse(isset($comp->prop2));
        $this->assertTrue(isset($comp->prop1));

    }

    function testRemoveByObj() {

        $comp = new VCalendar(array(), false);
        $comp->add('prop1','val1');
        $prop = $comp->add('prop2','val2');

        $comp->remove($prop);
        $this->assertFalse(isset($comp->prop2));
        $this->assertTrue(isset($comp->prop1));

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testRemoveNotFound() {

        $comp = new VCalendar(array(), false);
        $prop = $comp->createProperty('A','B');
        $comp->remove($prop);

    }
}
