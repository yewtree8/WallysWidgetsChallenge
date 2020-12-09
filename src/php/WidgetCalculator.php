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

    private static $KEY_D = ":";

    private $packSets;

    private $currentWidgetsLeft; //The value we'll be modifying.

    public function __construct($widgetsRequested)
    {
        $this->init($widgetsRequested);
        $this->calculateWidgets();
        $this->beginFancyTrim();
        $this->displayOrderSets();
    }

    private function displayOrderSets()
    {
        echo 'final orders are: ';
        foreach($this->orderSet as $packet) {
            echo $packet . ', ';
        }
    }


    private function debug($val)
    {
        echo $val . '<br>';
    }

    /**
     * Setup main values for the calculator.
     */
    private function init($widgetsRequested)
    {
        $this->requestedWidgets = $widgetsRequested;
        $this->currentWidgetsLeft = $widgetsRequested;
        $this->orderSet = [];
        $this->widgetSet = [250, 500, 1000, 2000, 5000];
        $this->packSets = []; //Key val for trimming.
        foreach($this->widgetSet as $widget) $this->packSets[$widget . self::$KEY_D] = 0;
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
        while($this->getCurrentLeft() >= 0) //Was going to use a for loop but that was silly.
        {
            for($i = 0 ; $i < count($flippedSet) ; $i++) //Lets go through these sets, got to remember it could be ANY number.
            {
                if($this->getCurrentLeft() <= $this->getMinimumWidgets())
                { //just get this out the way shall we.
                    if(!$this->getCurrentLeft() === 0)
                    {
                        $this->addToOrderSet($this->getMinimumWidgets());
                        $this->currentWidgetsLeft = 0;
                    }
                }
                if($this->getCurrentLeft()===0) return; //Just to not bother going through the logic.
                $currentPacket = $flippedSet[$i]; //So begin at 5000
                if($currentPacket <= $this->getCurrentLeft()) //We know we can remove it from the total.
                {
                    $this->addToOrderSet($currentPacket);
                    $this->decrementCurrentLeft($currentPacket);
                    //We gotta check though, it could be an input of 10k
                    if($this->getCurrentLeft() >= $currentPacket)
                    {
                        $i=-1; //Go back once in the loop, as we can send another same sized packet
                        continue;
                    }
                } else {
                    if($this->getCurrentLeft() < $this->getMinimumWidgets()) { //It must be but lets check
                        $this->addToOrderSet($this->getMinimumWidgets());
                        $this->decrementCurrentLeft($this->getMinimumWidgets()); //So it'll break.
                        return;
                    }
                }
            }
        }
    }

    private function beginFancyTrim()
    {
        $trimming = true;
       do {
            if(!$this->worthTrimming()) $trimming = false;
            $widgetClone = $this->widgetSet;
            sort($widgetClone);
            array_reverse($widgetClone); //From top to bottom.
            for($i = 0 ; $i <= count($this->widgetSet); $i++)
            {
                $currentTarget = $this->widgetSet[$i];
                foreach($this->packSets as $widgetPack => $quantity) {
                    if($quantity<=0)continue;
                    $trimmedKey = $this->trimPackKey($widgetPack);
                    $fullTotal = number_format($trimmedKey) * $quantity;
                    $remainderQuantity = $fullTotal; //The remainder of the pack.
                    if($fullTotal === $currentTarget) {
                        //Can assume it can be trimmed.
                        for($k = $quantity ; $k > 0 ; $k--) //Go backwards to trim the first
                        {
                            $remainderQuantity = $remainderQuantity - $quantity;
                            if($fullTotal === $currentTarget) {
                                sort($this->orderSet); //Need it in order to take em off
                                for($f = 0 ; $f < $quantity ; $f++) {
                                    $this->removeFromOrderSet($widgetPack, $f);
                                }
                                $this->addToOrderSet($fullTotal);
                                sort($this->orderSet);
                                if($fullTotal >= $remainderQuantity) {break;} else { continue;}
                                //To stop from over looping when unnesesary.
                            }
                        }
                    }else {
                        break; //No point going over
                    }
                }
            }

            $trimming = false;

       }while($trimming);
    }


    /**
     * Adds to order set, also creates packer.
     */
    private function addToOrderSet($toAdd)
    {
        array_push($this->orderSet, $toAdd);
        $targetKey = $this->packKey($toAdd);
        //Lets pack these together
        foreach($this->packSets as $widgetPack => $quantity)
        {
            if($widgetPack === $targetKey) {
                $newQuantity = $quantity + 1;
                $this->packSets[$widgetPack] = $newQuantity;
                break;
            }
        }
    }

    private function removeFromOrderSet($toRemove, $index)
    {
        unset($this->orderSet[$index]);
        $toRemove = (is_numeric($toRemove)) ? $this->packKey($toRemove) : $toRemove;
        foreach($this->packSets as $widgetPack => $quantity)
        {
            if($widgetPack === $toRemove) {
                $newQuantity = $quantity - 1;
                $this->packSets[$widgetPack] = $newQuantity;
                break;
            }
        }
    }

    private function worthTrimming()
    {
        foreach($this->packSets as $widget => $quantity)
        {
           if($quantity % 2 == 0) {
                return true;
            }
        }
        return false;
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


    private function packKey($val)
    {
        return $val . self::$KEY_D;
    }

    private function trimPackKey($keyVal)
    {
        return number_format(str_replace(self::$KEY_D, '', $keyVal));
    }


}

//$testValue = 501;
//$calculator = new WidgetCalculator($testValue);
