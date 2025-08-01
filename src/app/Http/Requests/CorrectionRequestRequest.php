<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;

class CorrectionRequestRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'requested_start_time' => ['required', 'date_format:H:i'],
            'requested_end_time' => ['required', 'date_format:H:i'],
            'requested_break1_start' => ['required', 'date_format:H:i'],
            'requested_break1_end' => ['required', 'date_format:H:i'],
            'requested_break2_start' => ['nullable', 'date_format:H:i'],
            'requested_break2_end' => ['nullable', 'date_format:H:i'],
            'requested_note' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            // 出勤・退勤
            'requested_start_time.required' => '出勤時間を入力してください',
            'requested_end_time.required' => '退勤時間を入力してください',
            'requested_start_time.date_format' => '出勤時間の形式が正しくありません（例：09:00）',
            'requested_end_time.date_format' => '退勤時間の形式が正しくありません（例：18:00）',

            // 休憩1
            'requested_break1_start.required' => '休憩1の開始時間を入力してください',
            'requested_break1_end.required' => '休憩1の終了時間を入力してください',
            'requested_break1_start.date_format' => '休憩1の開始時間の形式が正しくありません（例：12:00）',
            'requested_break1_end.date_format' => '休憩1の終了時間の形式が正しくありません（例：13:00）',

            // 休憩2
            'requested_break2_start.date_format' => '休憩2の開始時間の形式が正しくありません（例：15:00）',
            'requested_break2_end.date_format' => '休憩2の終了時間の形式が正しくありません（例：15:30）',

            // 備考
            'requested_note.required' => '備考を入力してください',
            'requested_note.string' => '備考は文字列で入力してください',
            'requested_note.max' => '備考は255文字以内で入力してください',
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('requested_start_time');
            $end = $this->input('requested_end_time');

            $breakStart = $this->input('requested_break_start');
            $breakEnd = $this->input('requested_break_end');

            $break2Start = $this->input('requested_break2_start');
            $break2End = $this->input('requested_break2_end');

            // 出退勤の整合性
            if ($start && $end && $start > $end) {
                $validator->errors()->add('requested_start_time', '出勤時間もしくは退勤時間が不適切な値です');
                $validator->errors()->add('requested_end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩1の整合性
            if ($breakStart && !$breakEnd) {
                $validator->errors()->add('requested_break_end', '休憩1の終了時間を入力してください');
            }

            if (!$breakStart && $breakEnd) {
                $validator->errors()->add('requested_break_start', '休憩1の開始時間を入力してください');
            }

            if ($breakStart && $breakEnd) {
                if ($start && $end && ($breakStart < $start || $breakEnd > $end)) {
                    $validator->errors()->add('requested_break_start', '休憩1の時間が勤務時間外です');
                    $validator->errors()->add('requested_break_end', '休憩1の時間が勤務時間外です');
                }

                if ($breakStart > $breakEnd) {
                    $validator->errors()->add('requested_break_start', '休憩1の開始が終了より後になっています');
                    $validator->errors()->add('requested_break_end', '休憩1の終了が開始より前になっています');
                }
            }

            // 休憩2の整合性（※任意入力）
            if ($break2Start && !$break2End) {
                $validator->errors()->add('requested_break2_end', '休憩2の終了時間を入力してください');
            }

            if (!$break2Start && $break2End) {
                $validator->errors()->add('requested_break2_start', '休憩2の開始時間を入力してください');
            }

            if ($break2Start && $break2End) {
                if ($start && $end && ($break2Start < $start || $break2End > $end)) {
                    $validator->errors()->add('requested_break2_start', '休憩2の時間が勤務時間外です');
                    $validator->errors()->add('requested_break2_end', '休憩2の時間が勤務時間外です');
                }

                if ($break2Start > $break2End) {
                    $validator->errors()->add('requested_break2_start', '休憩2の開始が終了より後になっています');
                    $validator->errors()->add('requested_break2_end', '休憩2の終了が開始より前になっています');
                }
            }
        });
    }
}
