<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'start_time'    => ['required', 'date_format:H:i', 'before:end_time'],
            'end_time'      => ['required', 'date_format:H:i', 'after:start_time'],

            'requested_break1_start' => ['required', 'date_format:H:i', 'before_or_equal:requested_break1_end'],
            'requested_break1_end'   => ['required', 'date_format:H:i', 'after_or_equal:requested_break1_start'],

            'break2_start'  => ['nullable', 'date_format:H:i', 'before_or_equal:break2_end'],
            'break2_end'    => ['nullable', 'date_format:H:i', 'after_or_equal:break2_start'],

            'note'          => ['required', 'string', 'max:255'], // ← ここを required に修正
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time');
            $end   = $this->input('end_time');

            // 休憩1の勤務時間外チェック
            foreach (['requested_break1'] as $break) {
                $b_start = $this->input("{$break}_start");
                $b_end   = $this->input("{$break}_end");

                if ($b_start && ($b_start < $start || $b_start > $end)) {
                    $validator->errors()->add("{$break}_start", '休憩時間が勤務時間外です。');
                }
                if ($b_end && ($b_end < $start || $b_end > $end)) {
                    $validator->errors()->add("{$break}_end", '休憩時間が勤務時間外です。');
                }
            }

            // 休憩2の片方だけ入力された場合のチェック
            $b2_start = $this->input('break2_start');
            $b2_end   = $this->input('break2_end');

            if ($b2_start && !$b2_end) {
                $validator->errors()->add('break2_end', '休憩2終了時間を入力してください。');
            }

            if (!$b2_start && $b2_end) {
                $validator->errors()->add('break2_start', '休憩2開始時間を入力してください。');
            }
        });
    }

    public function messages()
    {
        return [
            // 出勤・退勤
            'start_time.required' => '出勤時間を入力してください。',
            'end_time.required'   => '退勤時間を入力してください。',
            'start_time.before'   => '出勤時間は退勤時間より前である必要があります。',
            'end_time.after'      => '退勤時間は出勤時間より後である必要があります。',

            // 休憩1
            'requested_break1_start.required'        => '休憩1の開始時間を入力してください。',
            'requested_break1_start.before_or_equal' => '休憩1の開始は終了より前か同じ時刻である必要があります。',
            'requested_break1_end.required'          => '休憩1の終了時間を入力してください。',
            'requested_break1_end.after_or_equal'    => '休憩1の終了は開始より後か同じ時刻である必要があります。',

            // 休憩2
            'break2_start.date_format'               => '休憩2開始は「HH:MM」形式で入力してください。',
            'break2_end.date_format'                 => '休憩2終了は「HH:MM」形式で入力してください。',
            'break2_start.before_or_equal'           => '休憩2の開始は終了より前か同じ時刻である必要があります。',
            'break2_end.after_or_equal'              => '休憩2の終了は開始より後か同じ時刻である必要があります。',

            // 備考
            'note.required' => '備考を入力してください。',
            'note.max'      => '備考は255文字以内で入力してください。',
        ];
    }
}
