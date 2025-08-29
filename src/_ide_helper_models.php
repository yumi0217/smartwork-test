<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Attendance
 *
 * @property int $id
 * @property int $user_id
 * @property string $date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string $status
 * @property string|null $note
 * @property int $edited_by_admin
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BreakTime> $breaks
 * @property-read int|null $breaks_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CorrectionRequest> $correctionRequests
 * @property-read int|null $correction_requests_count
 * @property-read mixed $breaks_display
 * @property-read mixed $total_break_duration
 * @property-read mixed $total_work_duration
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEditedByAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attendance whereUserId($value)
 */
	class Attendance extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BreakTime
 *
 * @property int $id
 * @property int $attendance_id
 * @property \Illuminate\Support\Carbon|null $break_start
 * @property \Illuminate\Support\Carbon|null $break_end
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Attendance $attendance
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime query()
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime whereAttendanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime whereBreakEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime whereBreakStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BreakTime whereUpdatedAt($value)
 */
	class BreakTime extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CorrectionRequest
 *
 * @property int $id
 * @property int $attendance_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $requested_start_time
 * @property \Illuminate\Support\Carbon|null $requested_end_time
 * @property \Illuminate\Support\Carbon|null $requested_break1_start
 * @property \Illuminate\Support\Carbon|null $requested_break1_end
 * @property \Illuminate\Support\Carbon|null $requested_break2_start
 * @property \Illuminate\Support\Carbon|null $requested_break2_end
 * @property string|null $requested_note
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Attendance $attendance
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest query()
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereAttendanceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedBreak1End($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedBreak1Start($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedBreak2End($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedBreak2Start($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereRequestedStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CorrectionRequest whereUserId($value)
 */
	class CorrectionRequest extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property int $is_admin
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

