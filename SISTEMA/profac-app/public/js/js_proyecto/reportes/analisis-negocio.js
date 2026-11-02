document.addEventListener('alpine:init', () => {
    Alpine.data('analisisNegocio', (modulos) => ({
        modulos, seleccion: '', cargando: false, error: '', temporizador: null, receptor: null,
        get modulo() { return this.modulos.find(modulo => modulo.id === this.seleccion); },
        init() {
            this.receptor = (evento) => {
                const frame = this.$refs.contenedor.querySelector('iframe');
                if (evento.origin !== location.origin || evento.source !== frame?.contentWindow
                    || evento.data?.tipo !== 'analisis-modulo') return;
                this.terminar(evento.data.error || '');
            };
            window.addEventListener('message', this.receptor);
        },
        seleccionar() {
            clearTimeout(this.temporizador);
            this.$refs.contenedor.replaceChildren();
            this.error = '';
            this.cargando = false;
            if (!this.seleccion) return;
            if (!this.modulo?.permitido) {
                this.error = 'No tienes permiso para consultar este dashboard.';
                return;
            }
            this.cargando = true;
            const frame = document.createElement('iframe');
            frame.className = 'an-frame';
            frame.title = this.modulo.titulo;
            frame.src = this.modulo.url;
            frame.addEventListener('error', () => this.terminar('No se pudo cargar el módulo. Intenta nuevamente.'));
            frame.addEventListener('load', () => {
                const contenido = frame.contentDocument;
                if (!contenido?.querySelector('meta[name="analisis-modulo"]')) {
                    this.terminar('No se pudo abrir el dashboard. Verifica tu sesión y permisos e intenta nuevamente.');
                }
            });
            this.$refs.contenedor.append(frame);
            this.temporizador = setTimeout(() => this.terminar('El módulo está tardando demasiado en cargar. Puedes reintentar la consulta.'), 120000);
        },
        terminar(error) {
            clearTimeout(this.temporizador);
            this.error = error;
            this.cargando = false;
        },
        destroy() {
            clearTimeout(this.temporizador);
            window.removeEventListener('message', this.receptor);
        },
    }));
});
