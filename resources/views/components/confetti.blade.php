{{-- Confeti de celebración: unos segundos al entrar, sin bloquear clics. No corre si la persona pidió reducir el movimiento. --}}
<script>
    (() => {
        if (matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const canvas = document.createElement('canvas');
        canvas.setAttribute('aria-hidden', 'true');
        canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;pointer-events:none;z-index:60';
        document.body.appendChild(canvas);

        const ctx = canvas.getContext('2d');
        const resize = () => {
            canvas.width = innerWidth * devicePixelRatio;
            canvas.height = innerHeight * devicePixelRatio;
            ctx.setTransform(devicePixelRatio, 0, 0, devicePixelRatio, 0, 0);
        };
        resize();
        addEventListener('resize', resize);

        const colors = ['#6d28d9', '#a78bfa', '#ff6b5e', '#f59e0b', '#fde047', '#22c55e', '#38bdf8', '#ec4899'];
        const EMIT_MS = 3500, count = innerWidth < 640 ? 110 : 220;
        const pieces = Array.from({ length: count }, () => ({
            x: Math.random() * innerWidth,
            y: -20 - Math.random() * 80,
            delay: Math.random() * EMIT_MS,
            size: 6 + Math.random() * 7,
            color: colors[Math.floor(Math.random() * colors.length)],
            round: Math.random() < .25,
            speed: 130 + Math.random() * 170,
            drift: Math.random() * Math.PI * 2,
            spin: (Math.random() - .5) * 10,
        }));

        const start = performance.now();
        const frame = (now) => {
            const elapsed = now - start;
            ctx.clearRect(0, 0, innerWidth, innerHeight);

            let alive = false;
            for (const p of pieces) {
                const t = (elapsed - p.delay) / 1000;
                if (t < 0) { alive = true; continue; }
                const y = p.y + p.speed * t;
                if (y > innerHeight + 20) continue;
                alive = true;

                ctx.save();
                ctx.translate(p.x + Math.sin(p.drift + t * 2.5) * 28, y);
                ctx.rotate(p.spin * t);
                ctx.fillStyle = p.color;
                if (p.round) {
                    ctx.beginPath();
                    ctx.arc(0, 0, p.size / 2.4, 0, Math.PI * 2);
                    ctx.fill();
                } else {
                    // El ancho oscila para simular que el papel gira en el aire
                    ctx.fillRect(-p.size / 2, -p.size / 3, p.size * Math.abs(Math.cos(p.spin * t)), p.size / 1.5);
                }
                ctx.restore();
            }

            if (alive) {
                requestAnimationFrame(frame);
            } else {
                removeEventListener('resize', resize);
                canvas.remove();
            }
        };
        requestAnimationFrame(frame);
    })();
</script>
