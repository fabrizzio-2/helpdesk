<?php
// Verificar si el usuario está logueado
if (!is_user_logged_in()) {
    echo '<p>Debes iniciar sesión para crear un ticket.</p>';
    return;
}

// Obtener entidades y unidades
global $wpdb;
$entity_types = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpsts_entity_types");
$units = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpsts_units");

// Lista completa de prefijos telefónicos de países
$country_codes = array(
    '+1' => 'Estados Unidos/Canadá',
    '+7' => 'Rusia/Kazajistán',
    '+20' => 'Egipto',
    '+27' => 'Sudáfrica',
    '+30' => 'Grecia',
    '+31' => 'Países Bajos',
    '+32' => 'Bélgica',
    '+33' => 'Francia',
    '+34' => 'España',
    '+36' => 'Hungría',
    '+39' => 'Italia',
    '+40' => 'Rumania',
    '+41' => 'Suiza',
    '+43' => 'Austria',
    '+44' => 'Reino Unido',
    '+45' => 'Dinamarca',
    '+46' => 'Suecia',
    '+47' => 'Noruega',
    '+48' => 'Polonia',
    '+49' => 'Alemania',
    '+51' => 'Perú',
    '+52' => 'México',
    '+54' => 'Argentina',
    '+55' => 'Brasil',
    '+56' => 'Chile',
    '+57' => 'Colombia',
    '+58' => 'Venezuela',
    '+60' => 'Malasia',
    '+61' => 'Australia',
    '+62' => 'Indonesia',
    '+63' => 'Filipinas',
    '+64' => 'Nueva Zelanda',
    '+65' => 'Singapur',
    '+66' => 'Tailandia',
    '+81' => 'Japón',
    '+82' => 'Corea del Sur',
    '+86' => 'China',
    '+90' => 'Turquía',
    '+91' => 'India',
    '+92' => 'Pakistán',
    '+93' => 'Afganistán',
    '+94' => 'Sri Lanka',
    '+95' => 'Myanmar',
    '+98' => 'Irán',
    '+212' => 'Marruecos',
    '+213' => 'Argelia',
    '+216' => 'Túnez',
    '+218' => 'Libia',
    '+220' => 'Gambia',
    '+221' => 'Senegal',
    '+222' => 'Mauritania',
    '+223' => 'Malí',
    '+224' => 'Guinea',
    '+225' => 'Costa de Marfil',
    '+226' => 'Burkina Faso',
    '+227' => 'Níger',
    '+228' => 'Togo',
    '+229' => 'Benín',
    '+230' => 'Mauricio',
    '+231' => 'Liberia',
    '+232' => 'Sierra Leona',
    '+233' => 'Ghana',
    '+234' => 'Nigeria',
    '+235' => 'Chad',
    '+236' => 'República Centroafricana',
    '+237' => 'Camerún',
    '+238' => 'Cabo Verde',
    '+239' => 'Santo Tomé y Príncipe',
    '+240' => 'Guinea Ecuatorial',
    '+241' => 'Gabón',
    '+242' => 'República del Congo',
    '+243' => 'República Democrática del Congo',
    '+244' => 'Angola',
    '+245' => 'Guinea-Bissau',
    '+246' => 'Diego García',
    '+247' => 'Ascensión',
    '+248' => 'Seychelles',
    '+249' => 'Sudán',
    '+250' => 'Ruanda',
    '+251' => 'Etiopía',
    '+252' => 'Somalia',
    '+253' => 'Yibuti',
    '+254' => 'Kenia',
    '+255' => 'Tanzania',
    '+256' => 'Uganda',
    '+257' => 'Burundi',
    '+258' => 'Mozambique',
    '+260' => 'Zambia',
    '+261' => 'Madagascar',
    '+262' => 'Reunión',
    '+263' => 'Zimbabue',
    '+264' => 'Namibia',
    '+265' => 'Malaui',
    '+266' => 'Lesoto',
    '+267' => 'Botsuana',
    '+268' => 'Suazilandia',
    '+269' => 'Comoras',
    '+290' => 'Santa Elena',
    '+291' => 'Eritrea',
    '+297' => 'Aruba',
    '+298' => 'Islas Feroe',
    '+299' => 'Groenlandia',
    '+350' => 'Gibraltar',
    '+351' => 'Portugal',
    '+352' => 'Luxemburgo',
    '+353' => 'Irlanda',
    '+354' => 'Islandia',
    '+355' => 'Albania',
    '+356' => 'Malta',
    '+357' => 'Chipre',
    '+358' => 'Finlandia',
    '+359' => 'Bulgaria',
    '+370' => 'Lituania',
    '+371' => 'Letonia',
    '+372' => 'Estonia',
    '+373' => 'Moldavia',
    '+374' => 'Armenia',
    '+375' => 'Bielorrusia',
    '+376' => 'Andorra',
    '+377' => 'Mónaco',
    '+378' => 'San Marino',
    '+379' => 'Ciudad del Vaticano',
    '+380' => 'Ucrania',
    '+381' => 'Serbia',
    '+382' => 'Montenegro',
    '+383' => 'Kosovo',
    '+385' => 'Croacia',
    '+386' => 'Eslovenia',
    '+387' => 'Bosnia y Herzegovina',
    '+389' => 'Macedonia del Norte',
    '+420' => 'República Checa',
    '+421' => 'Eslovaquia',
    '+423' => 'Liechtenstein',
    '+500' => 'Islas Malvinas',
    '+501' => 'Belice',
    '+502' => 'Guatemala',
    '+503' => 'El Salvador',
    '+504' => 'Honduras',
    '+505' => 'Nicaragua',
    '+506' => 'Costa Rica',
    '+507' => 'Panamá',
    '+508' => 'San Pedro y Miquelón',
    '+509' => 'Haití',
    '+590' => 'Guadalupe',
    '+591' => 'Bolivia',
    '+592' => 'Guyana',
    '+593' => 'Ecuador',
    '+594' => 'Guayana Francesa',
    '+595' => 'Paraguay',
    '+596' => 'Martinica',
    '+597' => 'Surinam',
    '+598' => 'Uruguay',
    '+599' => 'Curazao',
    '+670' => 'Timor Oriental',
    '+672' => 'Antártida',
    '+673' => 'Brunéi',
    '+674' => 'Nauru',
    '+675' => 'Papúa Nueva Guinea',
    '+676' => 'Tonga',
    '+677' => 'Islas Salomón',
    '+678' => 'Vanuatu',
    '+679' => 'Fiyi',
    '+680' => 'Palaos',
    '+681' => 'Wallis y Futuna',
    '+682' => 'Islas Cook',
    '+683' => 'Niue',
    '+685' => 'Samoa',
    '+686' => 'Kiribati',
    '+687' => 'Nueva Caledonia',
    '+688' => 'Tuvalu',
    '+689' => 'Polinesia Francesa',
    '+690' => 'Tokelau',
    '+691' => 'Micronesia',
    '+692' => 'Islas Marshall',
    '+850' => 'Corea del Norte',
    '+852' => 'Hong Kong',
    '+853' => 'Macao',
    '+855' => 'Camboya',
    '+856' => 'Laos',
    '+880' => 'Bangladés',
    '+886' => 'Taiwán',
    '+960' => 'Maldivas',
    '+961' => 'Líbano',
    '+962' => 'Jordania',
    '+963' => 'Siria',
    '+964' => 'Irak',
    '+965' => 'Kuwait',
    '+966' => 'Arabia Saudita',
    '+967' => 'Yemen',
    '+968' => 'Omán',
    '+970' => 'Palestina',
    '+971' => 'Emiratos Árabes Unidos',
    '+972' => 'Israel',
    '+973' => 'Baréin',
    '+974' => 'Catar',
    '+975' => 'Bután',
    '+976' => 'Mongolia',
    '+977' => 'Nepal',
    '+992' => 'Tayikistán',
    '+993' => 'Turkmenistán',
    '+994' => 'Azerbaiyán',
    '+995' => 'Georgia',
    '+996' => 'Kirguistán',
    '+998' => 'Uzbekistán'
);
?>

<form id="support-ticket-form" class="wpsts-form">
    <div class="form-row">
        <div class="form-group col-md-3">
            <label for="ticket-name">Nombre:</label>
            <input type="text" id="ticket-name" name="name" required>
        </div>
        <div class="form-group col-md-3">
            <label for="ticket-surname">Apellidos:</label>
            <input type="text" id="ticket-surname" name="surname" required>
        </div>
        <div class="form-group col-md-3">
            <label for="ticket-email">Email:</label>
            <input type="email" id="ticket-email" name="email" required>
        </div>
        <div class="form-group col-md-3">
            <label for="ticket-phone">Teléfono:</label>
            <div class="input-group">
                <select id="ticket-phone-prefix" name="phone_prefix" class="form-control" style="width: 40%;">
                    <?php foreach ($country_codes as $code => $country): ?>
                        <option value="<?php echo esc_attr($code); ?>"><?php echo esc_html($code); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="tel" id="ticket-phone" name="phone" pattern="[0-9]{9}" maxlength="9" required style="width: 60%;">
            </div>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="ticket-entity-type">Tipo de Entidad a la que pertenece:</label>
            <select id="ticket-entity-type" name="entity_type" required>
                <option value="">Selecciona un tipo de entidad</option>
                <?php foreach ($entity_types as $entity_type): ?>
                    <option value="<?php echo esc_attr($entity_type->id); ?>"><?php echo esc_html($entity_type->name); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group col-md-6">
            <label for="ticket-entity-name">Nombre de la Entidad:</label>
            <input type="text" id="ticket-entity-name" name="entity_name" required>
        </div>
    </div>

    <div class="form-group">
        <label for="ticket-units">Unidades a las que se dirige:</label>
        <select id="ticket-units" name="units[]" multiple required>
            <?php foreach ($units as $unit): ?>
                <option value="<?php echo esc_attr($unit->id); ?>"><?php echo esc_html($unit->name); ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="ticket-subject">Asunto:</label>
        <input type="text" id="ticket-subject" name="subject" required>
    </div>

    <div class="form-group">
        <label for="ticket-description">Descripción Detallada:</label>
        <textarea id="ticket-description" name="description" rows="5" required></textarea>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Registrar Consulta</button>
        <button type="reset" class="btn btn-secondary">Resetear Formulario</button>
    </div>
</form>

<div id="ticket-message"></div>

<script>
jQuery(document).ready(function($) {
    $('#support-ticket-form').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        
        $.ajax({
            url: wpsts_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'wpsts_create_ticket',
                nonce: wpsts_ajax.nonce,
                ...formData
            },
            success: function(response) {
                if (response.success) {
                    window.location.href = response.data.redirect_url;
                } else {
                    $('#ticket-message').html('<p class="error">' + response.data + '</p>');
                }
            }
        });
    });

    // Aplicar máscara al campo de teléfono
    $('#ticket-phone').on('input', function() {
        $(this).val($(this).val().replace(/[^0-9]/g, ''));
    });
});
</script>