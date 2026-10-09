<?php

namespace App\Notifications;

use App\Models\Contract;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ContractReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(private readonly Contract $contract)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $hotelName = $this->contract->hotel->name;
        // Hotel-specific period names end with “ - Hotel name”; the hotel is
        // already shown separately in the notification, so do not repeat it.
        $seasonName = trim((string) preg_replace(
            '/\s+-\s+'.preg_quote($hotelName, '/').'$/iu',
            '',
            $this->contract->season->name
        ));

        return [
            'title' => 'Contract received',
            'message' => "We received the contract \"{$hotelName}\" for {$seasonName}.",
            'hotel_name' => $hotelName,
            'season_name' => $seasonName,
            'contract_id' => $this->contract->id,
        ];
    }
}
