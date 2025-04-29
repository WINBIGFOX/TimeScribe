<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | 验证语言行
    |--------------------------------------------------------------------------
    |
    | 以下语言行包含验证器类使用的默认错误消息。
    | 其中一些规则有多个版本，例如大小规则。请随意在此处调整这些消息。
    |
    */

    'accepted' => ':attribute 必须被接受。',
    'accepted_if' => '当 :other 为 :value 时，:attribute 必须被接受。',
    'active_url' => ':attribute 必须是一个有效的 URL。',
    'after' => ':attribute 必须是 :date 之后的日期。',
    'after_or_equal' => ':attribute 必须是等于或晚于 :date 的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、数字、破折号和下划线。',
    'alpha_num' => ':attribute 只能包含字母和数字。',
    'array' => ':attribute 必须是一个数组。',
    'ascii' => ':attribute 只能包含单字节字母数字字符和符号。',
    'before' => ':attribute 必须是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必须是等于或早于 :date 的日期。',
    'between' => [
        'array' => ':attribute 必须有 :min 到 :max 个项。',
        'file' => ':attribute 的大小必须在 :min 到 :max KB 之间。',
        'numeric' => ':attribute 的值必须在 :min 到 :max 之间。',
        'string' => ':attribute 的长度必须在 :min 到 :max 个字符之间。',
    ],
    'boolean' => ':attribute 字段必须为 true 或 false。',
    'can' => ':attribute 字段包含未经授权的值。',
    'confirmed' => ':attribute 确认不匹配。',
    'contains' => ':attribute 字段缺少必需的值。',
    'current_password' => '密码不正确。',
    'date' => ':attribute 必须是一个有效的日期。',
    'date_equals' => ':attribute 必须是等于 :date 的日期。',
    'date_format' => ':attribute 必须匹配格式 :format。',
    'decimal' => ':attribute 必须有 :decimal 位小数。',
    'declined' => ':attribute 必须被拒绝。',
    'declined_if' => '当 :other 为 :value 时，:attribute 必须被拒绝。',
    'different' => ':attribute 和 :other 必须不同。',
    'digits' => ':attribute 必须是 :digits 位数字。',
    'digits_between' => ':attribute 必须是 :min 到 :max 位数字。',
    'dimensions' => ':attribute 图片尺寸无效。',
    'distinct' => ':attribute 字段具有重复值。',
    'doesnt_end_with' => ':attribute 不能以以下任何一个结尾: :values。',
    'doesnt_start_with' => ':attribute 不能以以下任何一个开头: :values。',
    'email' => ':attribute 必须是一个有效的电子邮件地址。',
    'ends_with' => ':attribute 必须以以下任何一个结尾: :values。',
    'enum' => '所选的 :attribute 无效。',
    'exists' => '所选的 :attribute 无效。',
    'extensions' => ':attribute 字段必须具有以下扩展名之一: :values。',
    'file' => ':attribute 必须是一个文件。',
    'filled' => ':attribute 字段必须有值。',
    'gt' => [
        'array' => ':attribute 必须有多于 :value 个项。',
        'file' => ':attribute 必须大于 :value KB。',
        'numeric' => ':attribute 必须大于 :value。',
        'string' => ':attribute 必须多于 :value 个字符。',
    ],
    'gte' => [
        'array' => ':attribute 必须有 :value 个或更多项。',
        'file' => ':attribute 必须大于或等于 :value KB。',
        'numeric' => ':attribute 必须大于或等于 :value。',
        'string' => ':attribute 必须多于或等于 :value 个字符。',
    ],
    'hex_color' => ':attribute 必须是有效的十六进制颜色。',
    'image' => ':attribute 必须是一张图片。',
    'in' => '所选的 :attribute 无效。',
    'in_array' => ':attribute 必须存在于 :other 中。',
    'integer' => ':attribute 必须是一个整数。',
    'ip' => ':attribute 必须是一个有效的 IP 地址。',
    'ipv4' => ':attribute 必须是一个有效的 IPv4 地址。',
    'ipv6' => ':attribute 必须是一个有效的 IPv6 地址。',
    'json' => ':attribute 必须是一个有效的 JSON 字符串。',
    'list' => ':attribute 必须是一个列表。',
    'lowercase' => ':attribute 必须是小写字母。',
    'lt' => [
        'array' => ':attribute 必须少于 :value 个项。',
        'file' => ':attribute 必须小于 :value KB。',
        'numeric' => ':attribute 必须小于 :value。',
        'string' => ':attribute 必须少于 :value 个字符。',
    ],
    'lte' => [
        'array' => ':attribute 不能多于 :value 个项。',
        'file' => ':attribute 必须小于或等于 :value KB。',
        'numeric' => ':attribute 必须小于或等于 :value。',
        'string' => ':attribute 必须少于或等于 :value 个字符。',
    ],
    'mac_address' => ':attribute 必须是一个有效的 MAC 地址。',
    'max' => [
        'array' => ':attribute 不能多于 :max 个项。',
        'file' => ':attribute 不能大于 :max KB。',
        'numeric' => ':attribute 不能大于 :max。',
        'string' => ':attribute 不能多于 :max 个字符。',
    ],
    'max_digits' => ':attribute 不能超过 :max 位数字。',
    'mimes' => ':attribute 必须是类型为 :values 的文件。',
    'mimetypes' => ':attribute 必须是类型为 :values 的文件。',
    'min' => [
        'array' => ':attribute 必须至少有 :min 个项。',
        'file' => ':attribute 必须至少为 :min KB。',
        'numeric' => ':attribute 必须至少为 :min。',
        'string' => ':attribute 必须至少有 :min 个字符。',
    ],
    'min_digits' => ':attribute 必须至少有 :min 位数字。',
    'missing' => ':attribute 字段必须缺失。',
    'missing_if' => '当 :other 为 :value 时，:attribute 字段必须缺失。',
    'missing_unless' => '除非 :other 为 :value，否则 :attribute 字段必须缺失。',
    'missing_with' => '当 :values 存在时，:attribute 字段必须缺失。',
    'missing_with_all' => '当 :values 都存在时，:attribute 字段必须缺失。',
    'multiple_of' => ':attribute 必须是 :value 的倍数。',
    'not_in' => '所选的 :attribute 无效。',
    'not_regex' => ':attribute 格式无效。',
    'numeric' => ':attribute 必须是一个数字。',
    'password' => [
        'letters' => ':attribute 必须至少包含一个字母。',
        'mixed' => ':attribute 必须至少包含一个大写字母和一个小写字母。',
        'numbers' => ':attribute 必须至少包含一个数字。',
        'symbols' => ':attribute 必须至少包含一个符号。',
        'uncompromised' => '给定的 :attribute 已出现在数据泄露中。请选择不同的 :attribute。',
    ],
    'present' => ':attribute 字段必须存在。',
    'present_if' => '当 :other 为 :value 时，:attribute 字段必须存在。',
    'present_unless' => '除非 :other 为 :value，否则 :attribute 字段必须存在。',
    'present_with' => '当 :values 存在时，:attribute 字段必须存在。',
    'present_with_all' => '当 :values 都存在时，:attribute 字段必须存在。',
    'prohibited' => ':attribute 字段被禁止。',
    'prohibited_if' => '当 :other 为 :value 时，:attribute 字段被禁止。',
    'prohibited_if_accepted' => '当 :other 被接受时，:attribute 字段被禁止。',
    'prohibited_if_declined' => '当 :other 被拒绝时，:attribute 字段被禁止。',
    'prohibited_unless' => '除非 :other 在 :values 中，否则 :attribute 字段被禁止。',
    'prohibits' => ':attribute 字段禁止 :other 出现。',
    'regex' => ':attribute 格式无效。',
    'required' => ':attribute 字段是必填的。',
    'required_array_keys' => ':attribute 字段必须包含以下条目: :values。',
    'required_if' => '当 :other 为 :value 时，:attribute 字段是必填的。',
    'required_if_accepted' => '当 :other 被接受时，:attribute 字段是必填的。',
    'required_if_declined' => '当 :other 被拒绝时，:attribute 字段是必填的。',
    'required_unless' => '除非 :other 在 :values 中，否则 :attribute 字段是必填的。',
    'required_with' => '当 :values 存在时，:attribute 字段是必填的。',
    'required_with_all' => '当 :values 都存在时，:attribute 字段是必填的。',
    'required_without' => '当 :values 不存在时，:attribute 字段是必填的。',
    'required_without_all' => '当 :values 都不存在时，:attribute 字段是必填的。',
    'same' => ':attribute 必须与 :other 匹配。',
    'size' => [
        'array' => ':attribute 必须包含 :size 个项。',
        'file' => ':attribute 必须是 :size KB。',
        'numeric' => ':attribute 必须是 :size。',
        'string' => ':attribute 必须是 :size 个字符。',
    ],
    'starts_with' => ':attribute 必须以以下任何一个开头: :values。',
    'string' => ':attribute 必须是一个字符串。',
    'timezone' => ':attribute 必须是一个有效的时区。',
    'unique' => ':attribute 已经被占用。',
    'uploaded' => ':attribute 上传失败。',
    'uppercase' => ':attribute 必须是大写字母。',
    'url' => ':attribute 必须是一个有效的 URL。',
    'ulid' => ':attribute 必须是一个有效的 ULID。',
    'uuid' => ':attribute 必须是一个有效的 UUID。',

    /*
    |--------------------------------------------------------------------------
    | 自定义验证语言行
    |--------------------------------------------------------------------------
    |
    | 在这里，您可以使用“attribute.rule”约定为属性指定自定义验证消息来命名行。
    | 这使得为给定的属性规则快速指定特定的自定义语言行变得容易。
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 自定义验证属性
    |--------------------------------------------------------------------------
    |
    | 以下语言行用于将我们的属性占位符替换为更易于阅读的内容，
    | 例如“电子邮件地址”而不是“email”。这只是帮助我们使消息更具表现力。
    |
    */

    'attributes' => [],

];
