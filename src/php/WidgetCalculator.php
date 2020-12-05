<?php

/**
 * Class WidgetCalculator
 * Thought I'd go OO as it was mentioned
 * you'd like to keep it flexible.
 *
 * I'm going to be doing a lot of thinking out loud here so be prepared for over commenting.
 */
class WidgetCalculator
{
    private $requestedWidgets; //Requested widgets by the user.
    private $widgetSet; //array of accepted widget packs
    private $orderSet; //Set of the final order.

    private $minimumWidgets;
    private $maximumWidgets;

    private $currentWidgetsLeft; //The value we'll be modifying.

    public function __construct($widgetsRequested)
    {
        $this->init($widgetsRequested);
        $this->calculateWidgets();
        $this->trimPacks();
        $this->displayOrderSets();
    }

    private function displayOrderSets()
    {
        echo 'final orders are: ';
        foreach($this->orderSet as $packet) {
            echo $packet . ', ';
        }
    }


    /**
     * Setup main values for the calculator.
     */
    private function init($widgetsRequested)
    {
        $this->requestedWidgets = $widgetsRequested;
        $this->currentWidgetsLeft = $widgetsRequested;
        $this->orderSet = array();
        $this->widgetSet = array(250, 500, 1000, 2000, 5000);
        $this->minimumWidgets = $this->widgetSet[0];
        $this->maximumWidgets = $this->widgetSet[count($this->widgetSet)-1];
        sort($this->widgetSet); //PHP sometimes decides not to store things the way we put them, plus I gotta flip these later
    }

    /**
     * Here begins the main function
     */
    private function calculateWidgets()
    {
        $flippedSet = array_reverse($this->widgetSet); //Reverse, otherwise we'll end up giving too much stuff away.
        while($this->getCurrentLeft() > 0) //Was going to use a for loop but that was silly.
        {
            for($i = 0 ; $i < count($flippedSet) ; $i++) //Lets go through these sets, got to remember it could be ANY number.
            {
                if($this->getCurrentLeft() <= $this->getMinimumWidgets())
                { //just get this out the way shall we.
                    if(!$this->getCurrentLeft() === 0)
                    {
                        array_push($this->orderSet, $this->widgetSet[0]);
                        $this->currentWidgetsLeft = 0;
                        goto terminateloop;
                    }
                }
                if($this->getCurrentLeft()===0) goto terminateloop; //Just to not bother going through the logic.
                $currentPacket = $flippedSet[$i]; //So begin at 5000
                if($currentPacket <= $this->getCurrentLeft()) //We know we can remove it from the total.
                {
                    array_push($this->orderSet, $currentPacket); //Push those packets
                    $this->decrementCurrentLeft($currentPacket);
                    //We gotta check though, it could be an input of 10k
                    if($this->getCurrentLeft() >= $currentPacket)
                    {
                        $i=-1; //Go back once in the loop, as we can send another same sized packet
                        continue;
                    }
                } else {
                    if($this->getCurrentLeft() < $this->getMinimumWidgets()) { //It must be but lets check
                        array_push($this->orderSet, $this->widgetSet[0]);
                        $this->decrementCurrentLeft($this->widgetSet[0]); //So it'll break.
                        goto terminateloop;
                    }
                }
            }
        }
        terminateloop:
    }

    /**
     * May I just say that I had a eurika moment until you threw that lovely test case there..
     */
    private function trimPacks()
    {
        $trimming = true;
        $changed = false;
        while($trimming)
        {
            $newSet = array_reverse($this->orderSet); //Need these in acending order now.
            $finalArray = $newSet;
            for($i = 0 ; $i < count($newSet) ; $i++)//loop over the new set.
            {
                if($i!=count($newSet)-1){ //Not at the end of the array
                    $currentValue = $newSet[$i]; //This value
                    $nextValue = $newSet[$i+1]; //Next value.
                    $total = $currentValue + $nextValue; //The combined values
                    foreach($this->widgetSet as $widget)
                    {
                        if(($currentValue < $widget) && ($widget === $total)){ //We can trim.
                            unset($finalArray[$i]); unset($finalArray[$i+1]); //unset the last two values we added together.
                            array_push($finalArray, $widget); //push it to the final array.
                            sort($finalArray);
                            $changed = true; //Let it know it's been modified.
                            break;
                        }
                    }
                }
            }
            $trimming = false;
        }

        if($changed) $this->orderSet = $finalArray;
    }


    private function getCurrentLeft()
    {
        return $this->currentWidgetsLeft;
    }

    private function getMinimumWidgets()
    {
        return $this->minimumWidgets;
    }

    private function getMaximumWidgets()
    {
        return $this->maximumWidgets;
    }

    private function decrementCurrentLeft($quantity)
    {
        $this->currentWidgetsLeft -= $quantity;
    }

    public function getOrderSet()
    {
        return $this->orderSet;
    }


}

$testValue = 251;
$calculator = new WidgetCalculator($testValue);

