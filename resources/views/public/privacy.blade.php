<x-layouts.site title="Política de privacidad y condiciones de uso" description="Cómo trata la plataforma LION HUB EVENT los datos personales de quienes se registran, votan, comentan o apoyan emprendimientos.">
    @php
        $email = \App\Models\Setting::get('contact_email');
        $address = \App\Models\Setting::get('contact_address');
    @endphp
    <section class="ecos-soft-violeta pb-12 pt-28 lg:pt-36">
        <div class="container-x max-w-3xl">
            <span class="eyebrow"><x-icon name="shield-halved" /> Datos personales</span>
            <h1 class="page-title mt-3">Política de privacidad y condiciones de uso</h1>
            <p class="mt-4 text-ink-600">Última actualización: {{ \Illuminate\Support\Carbon::parse('2026-10-29')->translatedFormat('j \d\e F \d\e Y') }}</p>
        </div>
    </section>

    <section class="container-x max-w-3xl space-y-8 py-14 text-ink-700 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:text-lion-700 [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:space-y-1.5">
        <div class="space-y-3">
            <h2>Responsable</h2>
            <p>Fundación Universitaria Navarra – UNINAVARRA, {{ $address }}. Correo de contacto: <a href="mailto:{{ $email }}" class="font-semibold text-lion-700 underline">{{ $email }}</a>.</p>
            <p>Esta plataforma (LION HUB EVENT · Cátedra UNINAVARRA) trata los datos conforme a la Ley 1581 de 2012, el Decreto 1377 de 2013 y la política institucional de tratamiento de datos de UNINAVARRA.</p>
        </div>

        <div class="space-y-3">
            <h2>Qué datos recogemos</h2>
            <ul>
                <li>Al crear una cuenta: nombre, correo electrónico y, si lo indicas, programa académico, empresa u organización y teléfono.</li>
                <li>Si ingresas con Google: solo tu nombre, tu correo y el identificador de tu cuenta de Google. No accedemos a tus contactos, archivos ni a otros datos de Google.</li>
                <li>Tu actividad en la plataforma: votos, estrellas, comentarios e intenciones de apoyo a emprendimientos.</li>
                <li>Datos técnicos de seguridad, como la dirección IP, para evitar votos fraudulentos y abusos.</li>
            </ul>
        </div>

        <div class="space-y-3">
            <h2>Para qué los usamos</h2>
            <ul>
                <li>Gestionar tu cuenta y permitirte votar, comentar y apoyar emprendimientos.</li>
                <li>Calcular los resultados de la votación de la comunidad.</li>
                <li>Si registras una intención de apoyo, que el equipo de gestión de inversiones te contacte y te conecte con el emprendimiento.</li>
                <li>Enviarte correos de la plataforma, como la verificación de tu cuenta o el cambio de contraseña.</li>
            </ul>
            <p>No vendemos ni cedemos tus datos a terceros. Tu nombre solo aparece públicamente en tus comentarios y, si lo autorizas, en el ranking de aliados.</p>
        </div>

        <div class="space-y-3">
            <h2>Tus derechos</h2>
            <p>Puedes conocer, actualizar, rectificar y solicitar la eliminación de tus datos, o revocar la autorización. Puedes editar tus datos o eliminar tu cuenta desde tu perfil, o escribirnos a <a href="mailto:{{ $email }}" class="font-semibold text-lion-700 underline">{{ $email }}</a>.</p>
        </div>

        <div class="space-y-3">
            <h2>Conservación y seguridad</h2>
            <p>Guardamos tus datos mientras tengas una cuenta activa o mientras sean necesarios para las finalidades descritas. La plataforma usa conexión cifrada (HTTPS) y contraseñas cifradas.</p>
        </div>
    </section>

    {{-- ================= CONDICIONES DE USO ================= --}}
    <section id="condiciones" class="ecos-soft-azul scroll-mt-28 py-12">
        <div class="container-x max-w-3xl">
            <span class="eyebrow"><x-icon name="scale-balanced" /> Condiciones de uso</span>
            <h2 class="section-title mt-3">Contenido de la plataforma</h2>
            <p class="mt-4 text-ink-600">Al usar la plataforma aceptas también estas condiciones.</p>
        </div>
    </section>

    <section class="container-x max-w-3xl space-y-8 py-14 text-ink-700 [&_h2]:font-display [&_h2]:text-2xl [&_h2]:text-lion-700 [&_li]:ml-5 [&_li]:list-disc [&_p]:leading-relaxed [&_ul]:space-y-1.5">
        <div class="space-y-3">
            <h2>Logos, imágenes, videos y textos de los emprendimientos</h2>
            <p>Los nombres, logos, imágenes, videos pitch, descripciones y demás materiales de cada emprendimiento son creados y entregados por los equipos de estudiantes como parte de su proceso formativo en la Cátedra. Pueden haber sido elaborados por los propios estudiantes, con herramientas de inteligencia artificial generativa, o a partir de recursos de terceros (bancos de imágenes, plantillas, tipografías, música u otros).</p>
            <ul>
                <li>Cada equipo es responsable de contar con los derechos o licencias necesarios sobre el material que entrega y de respetar las condiciones de uso de las herramientas de inteligencia artificial que emplee.</li>
                <li>UNINAVARRA publica estos materiales con fines académicos y de divulgación, sin ánimo de lucro, y no verifica ni garantiza su originalidad, titularidad o registro.</li>
                <li>La publicación de un nombre o logo no significa que la marca esté registrada ni que UNINAVARRA respalde comercialmente el emprendimiento.</li>
                <li>Los derechos sobre las ideas y materiales de cada emprendimiento pertenecen a sus autores, conforme a la normativa de propiedad intelectual y a los reglamentos institucionales aplicables.</li>
            </ul>
        </div>

        <div class="space-y-3">
            <h2>Si algún contenido te afecta</h2>
            <p>Si consideras que una imagen, logo, video, texto o comentario publicado vulnera tus derechos de autor, de marca, tu imagen o tu privacidad, escríbenos a <a href="mailto:{{ $email }}" class="font-semibold text-lion-700 underline">{{ $email }}</a> indicando el enlace del contenido y el motivo. Lo revisaremos y, cuando corresponda, lo retiraremos o pediremos al equipo que lo reemplace, en el menor tiempo posible.</p>
        </div>

        <div class="space-y-3">
            <h2>Votos, ranking y resultados</h2>
            <ul>
                <li>La votación de la comunidad es una actividad académica: cada persona tiene un voto por edición y la plataforma aplica controles contra votos fraudulentos.</li>
                <li>UNINAVARRA puede anular votos, comentarios o cuentas que muestren indicios de fraude, suplantación o uso automatizado.</li>
                <li>Las notas y resultados finales se calculan con las reglas publicadas para cada edición (comité técnico y comunidad), y la decisión del comité y de la Cátedra es la que rige.</li>
            </ul>
        </div>

        <div class="space-y-3">
            <h2>Intenciones de apoyo e inversión</h2>
            <ul>
                <li>La plataforma no recibe pagos ni realiza transacciones. Una intención de apoyo solo expresa interés y permite que el equipo de gestión de inversiones ponga en contacto a las partes.</li>
                <li>La información de los emprendimientos es presentada por los estudiantes y no constituye asesoría financiera, jurídica ni recomendación de inversión. Cada inversionista o aliado decide bajo su propio criterio y responsabilidad.</li>
                <li>Cualquier acuerdo entre aliados y emprendedores es independiente de UNINAVARRA, salvo que la institución participe expresamente en él.</li>
            </ul>
        </div>

        <div class="space-y-3">
            <h2>Comentarios</h2>
            <p>Cada persona responde por lo que comenta. No se permiten contenidos ofensivos, discriminatorios, engañosos, publicitarios o que vulneren derechos de otros. UNINAVARRA puede ocultar o eliminar comentarios que incumplan estas reglas, sin previo aviso.</p>
        </div>

        <div class="space-y-3">
            <h2>Servicios de terceros</h2>
            <p>Los videos se muestran desde YouTube y el ingreso con Google lo presta Google; su uso se rige también por las condiciones y políticas de privacidad de esas empresas. La plataforma puede tener enlaces a sitios externos (redes sociales de los emprendimientos, por ejemplo) de cuyo contenido no somos responsables.</p>
        </div>

        <div class="space-y-3">
            <h2>Disponibilidad y cambios</h2>
            <p>Hacemos lo posible para que la plataforma funcione sin interrupciones, pero puede haber pausas por mantenimiento o fallas técnicas. Podemos actualizar esta política y estas condiciones; la fecha de la última actualización aparece al inicio de la página.</p>
        </div>
    </section>
</x-layouts.site>
