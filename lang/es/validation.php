<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| The following language lines contain the default error messages used by
	| the validator class. Some of these rules have multiple versions such
	| as the size rules. Feel free to tweak each of these messages here.
	|
	*/

	"accepted"             => "El :attribute debe ser aceptada.",
	"active_url"           => "El :attribute no es una URL válida.",
	"after"                => "El :attribute debe ser una fecha después :date.",
	"alpha"                => "El :attribute Sólo puede contener letras.",
	"alpha_dash"           => "El :attribute Sólo puede contener letras, números y guiones.",
	"ascii_only"           => "El :attribute Sólo puede contener letras, números y guiones.",
	"alpha_num"            => "El :attribute Sólo puede contener letras y números.",
	"array"                => "El :attribute debe ser una matriz.",
	"before"               => "El :attribute debe ser una fecha antes :date.",
	"between"              => [
		"numeric" => "El :attribute debe ser entre :min y :max.",
		"file"    => "El :attribute debe ser entre :min y :max kilobytes.",
		"string"  => "El :attribute debe ser entre :min y :max caracteres.",
		"array"   => "El :attribute debe tener entre :min y :max elementos.",
	],
	"boolean"              => ":attribute campo debe ser verdadera o falsa",
	"confirmed"            => ":attribute confirmación no coincide.",
	"date"                 => ":attribute no es una fecha válida.",
	"date_format"          => ":attribute no coincide con el formato :format.",
	"different"            => ":attribute y :other debe ser diferente.",
	"digits"               => ":attribute debe ser :digits dígitos.",
	"digits_between"       => ":attribute debe estar entre :min y :max dígitos.",
	'dimensions'           => 'La imagen tiene dimensiones inválidas (ancho) :min_width px (alto) :min_height px.',
	"email"                => ":attribute Debe ser una dirección válida de correo electrónico.",
	"filled"               => ":attribute es requerido.",
	"exists"               => "selected :attribute es inválido.",
	"image"                => ":attribute debe ser una imagen.",
	"in"                   => "seleccionado :attribute es inválido.",
	"integer"              => ":attribute debe ser un entero.",
	"ip"                   => ":attribute debe ser una dirección IP válida.",
	"max"                  => [
		"numeric" => "El :attribute no puede ser mayor que :max.",
		"file"    => "El :attribute no puede ser mayor que :max kilobytes.",
		"string"  => "El :attribute no puede ser mayor que :max caracteres.",
		"array"   => "El :attribute no puede tener más de :max elementos.",
	],
	"mimes"                => ":attribute debe ser un archivo de tipo: :values.",
	"min"                  => [
		"numeric" => "El :attribute al menos debe ser :min.",
		"file"    => "El :attribute al menos debe ser :min kilobytes.",
		"string"  => "El :attribute al menos debe ser :min caracteres.",
		"array"   => "El :attribute debe tener al menos :min elementos.",
	],
	"not_in"               => "El seleccionado :attribute es inválido.",
	"numeric"              => "El :attribute debe ser un número.",
	"regex"                => "El :attribute formato no es válido.",
	"required"             => ":attribute es un campo obligatorio.",
	"required_if"          => ":attribute es un campo obligatorio cuando :other es :value.",
	"required_with"        => ":attribute es un campo obligatorio cuando :values está presente.",
	"required_with_all"    => ":attribute es un campo obligatorio cuando :values está presente.",
	"required_without"     => ":attribute es un campo obligatorio cuando :values no está presente.",
	"required_without_all" => ":attribute Se requiere campo cuando ninguno de :values están presentes.",
	"same"                 => "El :attribute y :other debe coincidir.",
	"size"                 => [
		"numeric" => "El :attribute debe ser :size.",
		"file"    => "El :attribute debe ser :size kilobytes.",
		"string"  => "El :attribute debe ser :size caracteres.",
		"array"   => "El :attribute debe contener :size elementos.",
	],
	"unique"               => "El :attribute ya se ha tomado.",
	"url"                  => "El :attribute formato no es válido.",
	"timezone"             => "El :attribute debe ser una zona horaria válida.",
	"account_not_confirmed" => "Su cuenta no está confirmada, por favor revise su correo electrónico.",
	"user_suspended"        => "Su cuenta ha sido suspendida, por favor contáctenos si hay un error.",
	"letters"              => "El nombre de usuario debe contener al menos una letra o número",

	//"avatar_dimensions"  => "El Avatar must have a minimum of 180px width and 180px height.",
	//"cover_dimensions"  => "El Cover must have a minimum of 800px width and 600px height.",

	/*
	|--------------------------------------------------------------------------
	| Custom Validation Language Lines
	|--------------------------------------------------------------------------
	|
	| Here you may specify custom validation messages for attributes using the
	| convention "attribute.rule" to name the lines. This makes it quick to
	| specify a specific custom language line for a given attribute rule.
	|
	*/

	'custom' => [
		'attribute-name' => [
			'rule-name' => 'custom-message',
		],
	],


	/*
	|--------------------------------------------------------------------------
	| Custom Validation Attributes
	|--------------------------------------------------------------------------
	|
	| The following language lines are used to swap attribute place-holders
	| with something more reader friendly such as E-Mail Address instead
	| of "email". This simply helps us make messages a little cleaner.
	|
	*/

	'attributes' => [
		'agree_gdpr' => 'Casilla Estoy de acuerdo con el tratamiento de datos personales.',
		'full_name' => 'Nombre Completo', // Version 1.8
		'username'  => 'Nombre de usuario',
		'email'     => 'Correo electrónico',
		'old_password' => 'Contraseña antigua',
		'password'  => 'Contraseña',
		'password_confirmation' => 'Password Confirmation',
		'username_email' => 'Nombre de usuario o Correo electrónico',
		'website'   => 'Sitio Web',
		'location' => 'Ubicación',
		'countries_id' => 'País',
		'twitter'   => 'Twitter',
		'facebook'   => 'Facebook',
		'google'   => 'Google',
		'instagram'   => 'Instagram',
		'comment' => 'Comentario',
		'title' => 'Título',
		'tags'  => 'Etiquetas',
		'description' => 'Descripción',
		'photo' => 'Foto',
		'logo' => 'Logo',
		'index_image_top' => 'Imagen Header (Superior)',
		'index_image_bottom' => 'Imagen parte inferior',
		'amount' => 'Monto',
		'price' => 'Precio',
		'bank' => 'Banco',
		'email_paypal' => 'Correo PayPal',

		// Version 2.8
		'payment_gateway' => 'Pasarela de pago',

		// Version 3.5
		'message' => 'Mensaje',
		'subject' => 'Asunto',
	],

];
