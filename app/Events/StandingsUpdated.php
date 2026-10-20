<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event broadcast realtime pembaruan klasemen dan perolehan medali LT III
 */
class StandingsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public ?string $competitionId = null,
        public ?string $message = 'Klasemen perolehan medali telah diperbarui.'
    ) {}

    /**
     * Channel publik tempat event disiarkan
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('standings'),
        ];
    }

    /**
     * Nama event yang disiarkan ke client
     */
    public function broadcastAs(): string
    {
        return 'StandingsUpdated';
    }

    /**
     * Payload data siaran
     */
    public function broadcastWith(): array
    {
        return [
            'competition_id' => $this->competitionId,
            'message' => $this->message,
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
