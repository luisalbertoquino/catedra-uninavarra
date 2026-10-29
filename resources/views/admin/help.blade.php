<x-layouts.admin title="Ayuda · Manual de uso">
    @php
        // Cifras de la edición activa, para que el manual coincida con la configuración real
        $w = $edition?->gradeWeights() ?? ['community' => 30, 'jury' => 70, 'expert' => 0];
        $expert = $edition?->expert_title ?? 'Profesor experto';
        $ranges = $edition?->voteRanges() ?? \App\Models\Edition::DEFAULT_VOTE_RANGES;
        $ranges = collect($ranges)->values();
        $n = fn ($x) => rtrim(rtrim(number_format((float) $x, 2, ',', '.'), '0'), ',');
        $sections = [
            'inicio' => ['flag-checkered', 'Paso a paso del evento'],
            'roles' => ['user-shield', 'Roles y accesos'],
            'ediciones' => ['calendar-days', 'Ediciones'],
            'emprendimientos' => ['lightbulb', 'Emprendimientos'],
            'jurados' => ['gavel', 'Jurados y correo de bienvenida'],
            'evaluacion' => ['clipboard-check', 'Cómo evalúa el jurado'],
            'votacion' => ['check-to-slot', 'Votación de la comunidad'],
            'resultados' => ['ranking-star', 'Resultados y nota final'],
            'excel' => ['file-excel', 'Excel de resultados'],
            'apoyos' => ['hand-holding-dollar', 'Intenciones de apoyo'],
            'comentarios' => ['comments', 'Comentarios'],
            'usuarios' => ['users', 'Usuarios y administradores'],
            'configuracion' => ['sliders', 'Configuración'],
            'videos' => ['circle-play', 'Videos'],
            'preguntas' => ['circle-question', 'Preguntas frecuentes'],
        ];
    @endphp

    <div class="grid gap-6 lg:grid-cols-[15rem_minmax(0,1fr)]">
        {{-- Índice --}}
        <nav class="lg:sticky lg:top-6 lg:self-start">
            <div class="a-card p-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-500">Contenido</p>
                <ol class="space-y-0.5 text-sm">
                    @foreach ($sections as $id => [$icon, $label])
                        <li><a href="#{{ $id }}" class="flex items-center gap-2 rounded-lg px-2 py-1.5 text-slate-700 hover:bg-slate-100 hover:text-ink-900"><x-icon :name="$icon" fw /> {{ $label }}</a></li>
                    @endforeach
                </ol>
            </div>
        </nav>

        <div class="min-w-0 space-y-6 text-sm leading-relaxed text-slate-700 [&_h2]:flex [&_h2]:items-center [&_h2]:gap-2 [&_h2]:text-lg [&_h2]:font-semibold [&_h2]:text-ink-900 [&_h3]:mt-5 [&_h3]:font-semibold [&_h3]:text-ink-900 [&_li]:ml-5 [&_ol>li]:list-decimal [&_section]:scroll-mt-6 [&_ul>li]:list-disc [&_ol]:mt-2 [&_ol]:space-y-1.5 [&_ul]:mt-2 [&_ul]:space-y-1.5 [&_p]:mt-2">

            <section id="inicio" class="a-card p-6">
                <h2><x-icon name="flag-checkered" /> Paso a paso del evento</h2>
                <p>El orden recomendado para preparar y cerrar cada convocatoria de Cátedra UNINAVARRA:</p>
                <ol>
                    <li><b>Edición:</b> crea o revisa la convocatoria en <a class="font-medium text-ink-700 underline" href="{{ route('admin.editions.index') }}">Ediciones</a> (fechas, rúbrica y porcentajes de la nota) y pulsa <b>Activar</b>.</li>
                    <li><b>Emprendimientos:</b> cárgalos con <a class="font-medium text-ink-700 underline" href="{{ route('admin.import.create') }}">Importar Excel</a> o uno por uno, y agrega el enlace del video pitch de cada equipo.</li>
                    <li><b>Jurados:</b> créalos en <a class="font-medium text-ink-700 underline" href="{{ route('admin.juries.index') }}">Jurados</a> con el correo de bienvenida; marca al {{ $expert }}.</li>
                    <li><b>Votación:</b> decide si la comunidad vota en la página o en redes (YouTube, Facebook) desde <a class="font-medium text-ink-700 underline" href="{{ route('admin.results.index') }}">Resultados</a>.</li>
                    <li><b>Limpieza:</b> antes de abrir al público, borra evaluaciones, votos y cuentas de prueba (ver <a class="underline" href="#preguntas">Preguntas frecuentes</a>).</li>
                    <li><b>Seguimiento:</b> revisa el avance de los jurados en la matriz de evaluaciones y, si la votación es en redes, registra los votos.</li>
                    <li><b>Cierre:</b> marca los finalistas, asigna las posiciones después de la gran final, descarga el Excel y publica los resultados oficiales.</li>
                </ol>
            </section>

            <section id="roles" class="a-card p-6">
                <h2><x-icon name="user-shield" /> Roles y accesos</h2>
                <div class="mt-3 overflow-x-auto">
                    <table class="a-table">
                        <thead><tr><th>Rol</th><th>Cómo se crea</th><th>Qué puede hacer</th></tr></thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr><td class="font-semibold">Comunidad</td><td>Se registra sola (con correo o con Google)</td><td>Votar (si la votación es en la página), comentar y registrar apoyos.</td></tr>
                            <tr><td class="font-semibold">Inversionista / Aliado</td><td>Se registra sola, con teléfono obligatorio</td><td>Lo mismo que comunidad, más intenciones de inversión o donación.</td></tr>
                            <tr><td class="font-semibold">Jurado</td><td>Solo desde <b>Jurados</b></td><td>Entra a <code>/jurado</code> y califica cada video con la rúbrica. No vota como comunidad.</td></tr>
                            <tr><td class="font-semibold">{{ $expert }}</td><td>Jurado marcado como experto</td><td>Igual que un jurado; su nota aparece en su propia columna.</td></tr>
                            <tr><td class="font-semibold">Administrador</td><td>Solo desde <b>Usuarios</b> (cambiando el rol)</td><td>Todo el panel. Solo <b>{{ config('catedra.superadmin') }}</b> puede borrar cuentas.</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Nadie puede registrarse a sí mismo como jurado o administrador.</p>
            </section>

            <section id="ediciones" class="a-card p-6">
                <h2><x-icon name="calendar-days" /> Ediciones</h2>
                <p>Cada semestre es una edición (convocatoria). La edición <b>activa</b> es la que se ve en la portada y la que evalúan los jurados.</p>
                <ul>
                    <li><b>Siguiente convocatoria:</b> en la edición actual, copia la rúbrica, los porcentajes y el cronograma con las fechas corridas 6 meses. Revisa y guarda.</li>
                    <li><b>Votación y final:</b> fechas de inicio y cierre de la votación, fecha y lugar de la gran final, votos por persona, número de finalistas, dominio de correo permitido y la casilla <b>Votación de la comunidad en la página</b>.</li>
                    <li><b>Calificación:</b> porcentajes de la nota final (deben sumar 100 %), si el {{ $expert }} cuenta como un jurado más o tiene peso propio, y cómo se convierten los votos en nota (por rangos o relativa).</li>
                    <li><b>Rúbrica:</b> criterios, peso y puntaje máximo de cada uno. Si la cambias con evaluaciones hechas, las notas se recalculan.</li>
                    <li><b>Cronograma:</b> las fases que se muestran en la portada.</li>
                </ul>
            </section>

            <section id="emprendimientos" class="a-card p-6">
                <h2><x-icon name="lightbulb" /> Emprendimientos</h2>
                <ul>
                    <li><b>Lista:</b> haz clic en los encabezados para ordenar por nombre, grupo, equipo, votos, comentarios o apoyos. El buscador también busca por grupo.</li>
                    <li><b>Ficha:</b> nombre, frase corta, descripción, población objetivo, misión, visión, montos, grupo, categoría, logo, portada, galería, contacto, redes e integrantes (con foto opcional).</li>
                    <li><b>Video pitch:</b> pega el enlace de YouTube (también sirven Shorts, Vimeo o Google Drive).</li>
                    <li><b>Publicado:</b> solo los emprendimientos publicados se ven en el sitio.</li>
                </ul>
                <h3>Importar desde Excel</h3>
                <p>En <b>Importar Excel</b> eliges la edición y subes el Excel del portafolio (encabezados: Nombre · Programa · Grupo · Nombre de la Idea de Negocio · Descripción de la idea · Población objetivo · Misión · Visión · Monto estimado para iniciar · Monto que solicitaría a un inversionista · Logo). Volver a importar actualiza en lugar de duplicar.</p>
                <p class="rounded-lg bg-amber-50 p-3 text-amber-900"><x-icon name="triangle-exclamation" /> El Excel tiene datos personales de los estudiantes: guárdalo en un lugar privado y no lo compartas.</p>
            </section>

            <section id="jurados" class="a-card p-6">
                <h2><x-icon name="gavel" /> Jurados y correo de bienvenida</h2>
                <ol>
                    <li>En <b>Jurados</b>, escribe nombre, correo y organización. Marca <b>{{ $expert }}</b> si corresponde.</li>
                    <li>Deja marcada <b>Enviar correo de bienvenida</b> y la contraseña vacía: le llega un correo con un botón para <b>crear su propia contraseña</b>.</li>
                    <li>El enlace dura 7 días y sirve una sola vez. Si vence o se pierde, pulsa <b>Invitar</b> en la fila del jurado para enviar uno nuevo. Ese mismo botón sirve si olvidó su contraseña.</li>
                </ol>
                <p>Si escribes una contraseña, la plataforma la muestra una sola vez para que la copies. Si el correo no se puede enviar, aparece un aviso: usa <b>Invitar</b> para reintentarlo.</p>
                <p>El texto del correo se edita en <a class="font-medium text-ink-700 underline" href="{{ route('admin.settings.edit') }}">Configuración → Correo de bienvenida</a>, con vista previa. El saludo, el botón y la firma se agregan solos.</p>
                <h3>Borrar un jurado o sus evaluaciones</h3>
                <ul>
                    <li><b>Borrar el jurado</b> (en Jurados) elimina la cuenta y todas sus evaluaciones.</li>
                    <li>Para conservar la cuenta, ve a <b>Resultados → Ver evaluaciones de los jurados</b>: el ícono <x-icon name="trash-can" style="regular" /> junto a cada nota borra esa evaluación, y <b>Borrar todas</b> (al final de la columna del jurado) borra todas las suyas en la edición.</li>
                </ul>
            </section>

            <section id="evaluacion" class="a-card p-6">
                <h2><x-icon name="clipboard-check" /> Cómo evalúa el jurado</h2>
                <ol>
                    <li>Entra con su correo (o con Google) y llega a su panel en <code>/jurado</code>, con su avance (por ejemplo 7/15) y filtros Pendientes / Evaluados.</li>
                    <li>En cada emprendimiento ve el video (con botón de pantalla completa), la información del equipo y la rúbrica.</li>
                    <li>Califica cada criterio de 0 al máximo, con un decimal si quiere (por ejemplo 4,5), escribe un comentario opcional y pulsa <b>Guardar</b> o <b>Guardar y siguiente</b>.</li>
                    <li>Puede corregir sus notas hasta que se publiquen los resultados oficiales; en ese momento la evaluación se cierra.</li>
                </ol>
            </section>

            <section id="votacion" class="a-card p-6">
                <h2><x-icon name="check-to-slot" /> Votación de la comunidad</h2>
                <p>Hay dos formas, y se cambia con un botón en <a class="font-medium text-ink-700 underline" href="{{ route('admin.results.index') }}">Resultados</a> (recuadro <b>Votación en la página</b>) o con la casilla de la edición. La edición activa tiene ahora la votación <b>{{ $edition?->hasWebVoting() === false ? 'en redes' : 'en la página' }}</b>.</p>
                <div class="mt-3 grid gap-3 md:grid-cols-2">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="!mt-0 font-semibold text-ink-900"><x-icon name="heart" /> En la página</p>
                        <ul>
                            <li>Cada persona registrada y con correo verificado tiene {{ $edition?->max_votes_per_user ?? 1 }} voto(s) en la edición.</li>
                            <li>Para votar califica con estrellas y escribe un comentario de al menos {{ \App\Http\Controllers\VoteController::MIN_COMMENT }} caracteres.</li>
                            <li>Solo se vota entre la fecha de inicio y de cierre de la edición. Mientras esté abierta, puede retirar su voto y darlo a otro.</li>
                        </ul>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="!mt-0 font-semibold text-ink-900"><x-icon name="thumbs-up" /> En redes (YouTube, Facebook…)</p>
                        <ul>
                            <li>El botón «Votar» se cambia por <b>Votar en YouTube</b>, que lleva al video del equipo.</li>
                            <li>Un administrador escribe los votos (likes) de cada emprendimiento en la columna <b>Votos</b> de Resultados y pulsa <b>Guardar resultados</b>.</li>
                            <li>El mensaje que ve el público se edita en Configuración → Votación en redes.</li>
                        </ul>
                    </div>
                </div>
                <p>Si vuelves a habilitar la votación en la página, los votos registrados a mano se conservan y se suman a los de la página.</p>
            </section>

            <section id="resultados" class="a-card p-6">
                <h2><x-icon name="ranking-star" /> Resultados y nota final</h2>
                <p class="rounded-xl border border-dashed border-lion-300 bg-lion-50/60 p-4 text-center text-base font-semibold text-ink-900">
                    @if ($edition?->expertCountsAsJury() ?? true)
                        Nota final = Comité técnico × {{ $w['jury'] }} % + Comunidad × {{ $w['community'] }} %
                    @else
                        Nota final = Jurados × {{ $w['jury'] }} % + {{ $expert }} × {{ $w['expert'] }} % + Comunidad × {{ $w['community'] }} %
                    @endif
                </p>
                <ul>
                    @if ($edition?->expertCountsAsJury() ?? true)
                        <li><b>Comité técnico:</b> promedio simple de los jurados y el {{ $expert }}; cada uno pesa igual. Cada evaluación se lleva a escala 1 a 5 según la rúbrica.</li>
                    @endif
                    <li><b>Comunidad:</b>
                        @if ($edition?->vote_grade_mode === 'relativo')
                            el emprendimiento más votado obtiene 5 y los demás en proporción.
                        @else
                            nota según los votos:
                            {{ $ranges->map(fn ($r, $i) => ($ranges->has($i + 1) ? $r['min'].' a '.($ranges[$i + 1]['min'] - 1) : $r['min'].' o más').' votos = '.$n($r['grade']))->implode(' · ') }}.
                        @endif
                    </li>
                    <li><b>Parcial:</b> si a un emprendimiento le falta un componente (por ejemplo, un jurado no lo ha evaluado), su peso se reparte entre los demás y la nota sale marcada como parcial.</li>
                </ul>
                <h3>La tabla de Resultados</h3>
                <p>Va ordenada por nota final. Columnas: una por cada jurado y el {{ $expert }} con su nota, luego el comité técnico, los votos, la nota de la comunidad y la nota final. Después:</p>
                <ul>
                    <li><b>Manual 0-100:</b> solo si un emprendimiento no tiene evaluaciones y el comité entrega una nota por fuera de la plataforma.</li>
                    <li><b>Marcar top {{ $edition?->finalists_count ?? 5 }} como finalistas:</b> marca automáticamente a los mejores según la nota final. También puedes marcarlos a mano.</li>
                    <li><b>Posición:</b> el puesto después de la gran final (1 = ganador).</li>
                    <li><b>Publicar resultados oficiales:</b> el ranking público pasa a mostrar la nota final y se cierra la evaluación de los jurados.</li>
                </ul>
                <p>Con <b>Ver evaluaciones de los jurados</b> ves la matriz de notas; al pasar el mouse por una celda aparece el detalle por criterio y el comentario.</p>
            </section>

            <section id="excel" class="a-card p-6">
                <h2><x-icon name="file-excel" /> Excel de resultados</h2>
                <p>El botón <b>Exportar Excel</b> de Resultados descarga un archivo con tres hojas, en el orden del ranking:</p>
                <ol>
                    <li><b>Resultados:</b> la misma tabla de la pantalla (nota de cada jurado y del experto, comité, votos, comunidad, nota final, finalista y posición).</li>
                    <li><b>Rúbrica por jurado:</b> por cada evaluador, el puntaje que dio en cada criterio (Innovación, Viabilidad comercial, etc.) y su nota.</li>
                    <li><b>Comentarios:</b> lo que escribió cada evaluador a cada emprendimiento.</li>
                </ol>
                <p>Las notas quedan como números, así que puedes ordenar, filtrar o sacar promedios en Excel.</p>
            </section>

            <section id="apoyos" class="a-card p-6">
                <h2><x-icon name="hand-holding-dollar" /> Intenciones de apoyo</h2>
                <p>La plataforma no recibe pagos. Un inversionista o aliado registra su intención (inversión, donación, asesoría, contactos, mercadeo, etc.) y la persona encargada lo contacta.</p>
                <ol>
                    <li>La intención llega como <b>Pendiente</b> a <a class="font-medium text-ink-700 underline" href="{{ route('admin.pledges.index') }}">Intenciones de apoyo</a>.</li>
                    <li>Cambia el estado según avance: Contactado → En proceso → <b>Concretado</b> o Descartado, con notas internas.</li>
                    <li>Solo los apoyos <b>concretados</b> de quienes autorizaron aparecer cuentan en el ranking de inversionistas.</li>
                </ol>
            </section>

            <section id="comentarios" class="a-card p-6">
                <h2><x-icon name="comments" /> Comentarios</h2>
                <p>En <a class="font-medium text-ink-700 underline" href="{{ route('admin.comments.index') }}">Comentarios</a> puedes ocultar, publicar o eliminar opiniones. Retira los ofensivos, publicitarios o que vulneren derechos de otros (así lo dicen las condiciones de uso).</p>
            </section>

            <section id="usuarios" class="a-card p-6">
                <h2><x-icon name="users" /> Usuarios y administradores</h2>
                <ul>
                    <li><b>Nuevo administrador:</b> la persona se registra (o la creas como jurado) y en <a class="font-medium text-ink-700 underline" href="{{ route('admin.users.index') }}">Usuarios</a> le cambias el rol a administrador. Luego pulsa <b>Invitar</b> para enviarle el correo con el enlace de contraseña.</li>
                    <li><b>Borrar cuentas:</b> solo el administrador principal ({{ config('catedra.superadmin') }}) ve el botón de borrar. Se borran también los votos, comentarios y apoyos de esa cuenta.</li>
                    <li><b>Ingreso con Google:</b> cualquier persona puede entrar con su cuenta de Google. Si el correo ya existe (también jurados y administradores), entra con esa cuenta; si es nueva, acepta el tratamiento de datos y elige su perfil.</li>
                </ul>
            </section>

            <section id="configuracion" class="a-card p-6">
                <h2><x-icon name="sliders" /> Configuración</h2>
                <p>En <a class="font-medium text-ink-700 underline" href="{{ route('admin.settings.edit') }}">Configuración</a> se editan sin tocar código:</p>
                <ul>
                    <li>Título y frase de la portada.</li>
                    <li>Datos de la persona encargada de las inversiones (nombre, WhatsApp y correo).</li>
                    <li>Mensaje de la votación en redes.</li>
                    <li>Texto del correo de bienvenida para jurados y administradores (con vista previa).</li>
                    <li>Contacto general y texto del pie de página.</li>
                </ul>
                <p>Las <b>categorías</b> (sectores) se editan en <a class="font-medium text-ink-700 underline" href="{{ route('admin.categories.index') }}">Categorías</a>. La política de privacidad y las condiciones de uso están en <a class="font-medium text-ink-700 underline" href="{{ route('privacy') }}" target="_blank">/privacidad</a>.</p>
            </section>

            <section id="videos" class="a-card p-6">
                <h2><x-icon name="circle-play" /> Videos</h2>
                <ul>
                    <li>Los videos verticales de YouTube (Shorts) se muestran con el reproductor de Shorts, que no trae botón de pantalla completa. Por eso debajo de cada video está <b>Ver en pantalla completa</b>.</li>
                    <li>En iPhone ese botón abre el video en YouTube, porque el navegador no permite la pantalla completa desde la página.</li>
                    <li>El video promocional de la portada se configura en la edición (solo YouTube).</li>
                </ul>
            </section>

            <section id="preguntas" class="a-card p-6">
                <h2><x-icon name="circle-question" /> Preguntas frecuentes</h2>
                <h3>Un jurado no recibió el correo de bienvenida.</h3>
                <p>Pídele que revise la carpeta de spam o promociones. Si no aparece, pulsa <b>Invitar</b> otra vez en Jurados. También puede entrar con Google si su correo es de Gmail o de Google Workspace.</p>
                <h3>Una persona no puede votar.</h3>
                <p>Debe tener sesión iniciada y el correo verificado, la votación debe estar abierta (fechas de la edición) y en la página. Jurados y administradores no votan. El botón le explica el motivo.</p>
                <h3>¿Cómo borro los datos de prueba antes del evento?</h3>
                <ul>
                    <li><b>Evaluaciones de prueba:</b> Resultados → Ver evaluaciones de los jurados → <b>Borrar todas</b> en la columna del jurado, o borra el jurado de prueba en Jurados.</li>
                    <li><b>Cuentas y votos de prueba:</b> el administrador principal borra la cuenta en Usuarios (se van sus votos y comentarios).</li>
                    <li><b>Todos los votos de la edición:</b> lo hace el soporte técnico en el servidor con <code>php artisan catedra:borrar-votos --con-comentarios</code>.</li>
                </ul>
                <h3>¿Puedo cambiar los porcentajes de la nota a mitad del evento?</h3>
                <p>Sí, en la edición. Las notas se recalculan solas con los nuevos porcentajes; las calificaciones de los jurados no cambian.</p>
                <h3>Un emprendimiento no tiene video.</h3>
                <p>Agrega el enlace en su ficha cuando el equipo lo entregue. Mientras tanto, los jurados ven la información escrita.</p>
            </section>
        </div>
    </div>
</x-layouts.admin>
