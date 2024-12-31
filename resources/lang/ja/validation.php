<?php
return [
    'required' => ':attributeは必須です。',
    'image' => ':attributeではないファイルが指定されています。',
    'mimes' => ':attributeは:values形式のファイルを指定してください。',
    'email' => ':attributeは有効なメールアドレスでなければなりません。',
    'max' => [
        'string' => ':attributeは:max文字以内でなければなりません。',
    ],
    'min' => [
        'string' => ':attributeは:min文字以上でなければなりません。',
    ],
    'attributes' => [
        'image' => '画像',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
    ],
];