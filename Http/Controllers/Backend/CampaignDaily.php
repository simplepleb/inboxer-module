<?php

namespace Modules\Marketing\Http\Controllers\Backend;

use Modules\Marketing\Entities\Campaign;
use Modules\Marketing\Entities\CampaignsListsSegment;
use Modules\Marketing\Entities\Customer;
use Modules\Marketing\Entities\MailList;
use Modules\Marketing\Entities\Setting;
use Illuminate\Http\Request;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;
use Modules\Marketing\Library\Log as MailLog;
use Illuminate\Support\Facades\Log as LaravelLog;

use Validator;
use Illuminate\Validation\ValidationException;
use Storage;


class CampaignDaily extends Controller
{
    public function create($html = null)
    {
        $mail_list_id = 1;

        $html = '<h1>Welcome...</h1><br>This is a test from the route to get ready to move <a href="https://traders.fxstockbroker.com">Daily Emails</a>  to the campaign system.
                    <br />To <a href="{UNSUBSCRIBE_URL}">Unsubscribe Here</a>
                ';
        $customer = Customer::find(1);
        $campaign = new \Modules\Marketing\Entities\Campaign([
            'track_open' => true,
            'track_click' => true,
            'sign_dkim' => true,
        ]);

        $campaign->name = trans('Daily News - '.date('l \t\h\e jS') );
        $campaign->customer_id = $customer->id;
        $campaign->status = \Modules\Marketing\Entities\Campaign::STATUS_NEW;
        $campaign->type = 'regular';
        //$campaign->save();
        $campaign->default_mail_list_id = '1';


        $campaign->save();

        event(new \Modules\Marketing\Events\CampaignUpdated($campaign));

        $campaign->find($campaign->id);

        $campaign->from_name =  $campaign->defaultMailList->from_name;
        $campaign->from_email = $campaign->defaultMailList->from_email;
        $campaign->reply_to = $campaign->defaultMailList->from_email;
        $campaign->subject =  $campaign->defaultMailList->default_subject;

        $campaign->log('created', $customer);

        $lists_segment = new CampaignsListsSegment();
        $lists_segment->campaign_id = $campaign->id;
        $lists_segment->mail_list_id = $mail_list_id;
        $lists_segment->save();


        $campaign->html = $html;

        $delivery_date = \Modules\Marketing\Library\Tool::dateTime(\Carbon\Carbon::now())->format('Y-m-d');
        $delivery_time = \Modules\Marketing\Library\Tool::dateTime(\Carbon\Carbon::now()->addHour())->format('H:i');

        $time = \Modules\Marketing\Library\Tool::systemTimeFromString($delivery_date.' '.$delivery_time);
        $campaign->run_at = $time;
        $campaign->save();

        $campaign->requeue();





    }
}
