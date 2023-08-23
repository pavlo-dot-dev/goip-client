<?php

namespace PavloDotDev\GoipClient\Entities;

class USSDList extends DataTable
{
    public function current(): USSDItem
    {
        $data = array_combine($this->columns, $this->items[$this->position]);

        return new USSDItem(
            id: $data['choose'],
            termId: $data['terminal'],
            time: $data['send_time'],
            command: $data['ussd_cmd'],
            message: $data['ussd_return'] ?: null,
            error: $data['error_msg'] ?: null,
        );
    }
}
