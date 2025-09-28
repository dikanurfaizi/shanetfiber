<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SnmpTestModal extends Component
{
    public $id;
    public $title;
    public $closeText;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($id = 'snmpTestModal', $title = 'SNMP Connection Test', $closeText = 'Close')
    {
        $this->id = $id;
        $this->title = $title;
        $this->closeText = $closeText;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.snmp-test-modal');
    }
}
