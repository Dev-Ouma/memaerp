<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['campaign_code', 'title', 'target_audience', 'dispatch_channels', 'trigger_schedule', 'total_recipients', 'status'])]
final class RegistrationReminderCampaign extends Model {}
