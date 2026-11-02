<meta name="analisis-modulo" content="1">
<script>
    (() => {
        let pendientes = 0, cargado = false, terminado = false, error = false, espera;
        const comprobar = () => {
            clearTimeout(espera);
            if (!cargado || pendientes || terminado) return;
            espera = setTimeout(() => {
                terminado = true;
                parent.postMessage({
                    tipo: 'analisis-modulo',
                    error: error ? 'El módulo abrió, pero alguna consulta falló. Revisa los avisos del reporte o reintenta.' : '',
                }, location.origin);
            }, 400);
        };
        const iniciar = () => { if (!terminado) { pendientes++; clearTimeout(espera); } };
        const finalizar = (fallo) => {
            if (terminado) return;
            error = error || fallo;
            pendientes--;
            comprobar();
        };
        const fetchOriginal = window.fetch;
        window.fetch = async (...args) => {
            iniciar();
            try {
                const respuesta = await fetchOriginal(...args);
                finalizar(!respuesta.ok);
                return respuesta;
            } catch (e) {
                finalizar(true);
                throw e;
            }
        };
        const enviar = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.send = function (...args) {
            iniciar();
            this.addEventListener('loadend', () => finalizar(this.status === 0 || this.status >= 400), { once: true });
            try { return enviar.apply(this, args); }
            catch (e) { finalizar(true); throw e; }
        };
        window.addEventListener('error', () => { error = true; }, true);
        window.addEventListener('unhandledrejection', () => { error = true; });
        window.addEventListener('load', () => { cargado = true; comprobar(); }, { once: true });
    })();
</script>
