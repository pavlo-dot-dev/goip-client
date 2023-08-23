<?php

namespace PavloDotDev\GoipClient\Entities;

class InboxSMS extends DataTable
{
    public function current(): InboxSMSItem
    {
        $data = array_combine($this->columns, $this->items[$this->position]);

        return new InboxSMSItem(
            id: $data['choose'],
            time: $data['receive_time'],
            smscNumber: $data['smsc_number'],
            sourceNumber: $data['source_number'],
            termId: $data['receive_goip'],
            text: $data['sms_text']
        );
    }
}
