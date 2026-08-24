@php
    $initialAlerts = [];
    if (session('success')) {
        $initialAlerts[] = ['id' => uniqid('pill_'), 'message' => session('success'), 'type' => 'success', 'title' => 'Task Completed'];
    }
    if (session('status')) {
        $initialAlerts[] = ['id' => uniqid('pill_'), 'message' => session('status'), 'type' => 'info', 'title' => 'Status Update'];
    }
    if (session('error')) {
        $initialAlerts[] = ['id' => uniqid('pill_'), 'message' => session('error'), 'type' => 'error', 'title' => 'Action Failed'];
    }
    if (session('warning')) {
        $initialAlerts[] = ['id' => uniqid('pill_'), 'message' => session('warning'), 'type' => 'warning', 'title' => 'Attention'];
    }
    if (session('message')) {
        $initialAlerts[] = ['id' => uniqid('pill_'), 'message' => session('message'), 'type' => 'info', 'title' => 'Notification'];
    }
@endphp

<div x-data="alertPillSystem({{ Js::from($initialAlerts) }})" 
     x-cloak
     class="fixed right-3 sm:right-6 top-1/2 -translate-y-1/2 z-[9999] flex flex-col gap-2.5 items-end pointer-events-none max-w-sm sm:max-w-md w-full select-none"
     aria-live="polite"
     id="devfolio-alert-pill-stack">

    <template x-for="alert in alerts" :key="alert.id">
        <div x-show="alert.visible"
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-x-12 scale-90"
             x-transition:enter-end="opacity-100 translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 translate-x-0 scale-100"
             x-transition:leave-end="opacity-0 translate-x-12 scale-90"
             @mouseenter="pauseAlert(alert.id)"
             @mouseleave="resumeAlert(alert.id)"
             class="pointer-events-auto group relative w-full sm:w-auto min-w-[280px] max-w-sm rounded-2xl sm:rounded-full bg-slate-950/95 backdrop-blur-xl border p-2.5 sm:px-4 sm:py-2.5 shadow-2xl transition-all hover:scale-[1.02]"
             :class="{
                 'border-emerald-500/40 shadow-emerald-950/60 shadow-lg text-emerald-100': alert.type === 'success',
                 'border-rose-500/40 shadow-rose-950/60 shadow-lg text-rose-100': alert.type === 'error',
                 'border-amber-500/40 shadow-amber-950/60 shadow-lg text-amber-100': alert.type === 'warning',
                 'border-cyan-500/40 shadow-cyan-950/60 shadow-lg text-cyan-100': alert.type === 'info' || !alert.type
             }">

            <!-- Ambient Glow Capsule Background -->
            <div class="absolute inset-0 rounded-2xl sm:rounded-full opacity-20 pointer-events-none blur-md transition-opacity group-hover:opacity-40"
                 :class="{
                     'bg-emerald-500': alert.type === 'success',
                     'bg-rose-500': alert.type === 'error',
                     'bg-amber-500': alert.type === 'warning',
                     'bg-cyan-500': alert.type === 'info' || !alert.type
                 }">
            </div>

            <!-- Pill Inner Flex Layout -->
            <div class="relative flex items-center gap-3">
                <!-- Icon Capsule -->
                <div class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center border shadow-inner"
                     :class="{
                         'bg-emerald-500/20 border-emerald-400/40 text-emerald-400': alert.type === 'success',
                         'bg-rose-500/20 border-rose-400/40 text-rose-400': alert.type === 'error',
                         'bg-amber-500/20 border-amber-400/40 text-amber-400': alert.type === 'warning',
                         'bg-cyan-500/20 border-cyan-400/40 text-cyan-400': alert.type === 'info' || !alert.type
                     }">
                    <!-- Success Icon -->
                    <template x-if="alert.type === 'success'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </template>

                    <!-- Error Icon -->
                    <template x-if="alert.type === 'error'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </template>

                    <!-- Warning Icon -->
                    <template x-if="alert.type === 'warning'">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </template>

                    <!-- Info Icon -->
                    <template x-if="alert.type === 'info' || !alert.type">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </template>
                </div>

                <!-- Text Content -->
                <div class="flex-1 min-w-0 pr-1">
                    <div class="flex items-center gap-1.5 font-mono">
                        <span class="text-[9px] uppercase font-black tracking-widest px-1.5 py-0.5 rounded-full border"
                              :class="{
                                  'bg-emerald-500/20 border-emerald-400/30 text-emerald-300': alert.type === 'success',
                                  'bg-rose-500/20 border-rose-400/30 text-rose-300': alert.type === 'error',
                                  'bg-amber-500/20 border-amber-400/30 text-amber-300': alert.type === 'warning',
                                  'bg-cyan-500/20 border-cyan-400/30 text-cyan-300': alert.type === 'info' || !alert.type
                              }"
                              x-text="alert.title || (alert.type === 'success' ? 'TASK COMPLETED' : (alert.type === 'error' ? 'ACTION FAILED' : (alert.type === 'warning' ? 'ATTENTION' : 'NOTICE')))">
                        </span>
                    </div>
                    <p class="text-xs font-semibold text-white/95 mt-0.5 truncate leading-tight" x-text="alert.message"></p>
                </div>

                <!-- Close Action Button -->
                <button type="button" 
                        @click="removeAlert(alert.id)" 
                        class="shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-colors cursor-pointer"
                        title="Dismiss alert">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Auto-Dismiss Decay Progress Bar -->
            <div class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full overflow-hidden bg-white/5 pointer-events-none">
                <div class="h-full rounded-full transition-all linear"
                     :style="`width: ${alert.progress}%; background-color: ${alert.type === 'success' ? '#10B981' : (alert.type === 'error' ? '#F43F5E' : (alert.type === 'warning' ? '#F59E0B' : '#06B6D4'))}`">
                </div>
            </div>
        </div>
    </template>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('alertPillSystem', (initialAlerts = []) => ({
            alerts: [],

            init() {
                // Initialize session flash alerts
                if (Array.isArray(initialAlerts) && initialAlerts.length > 0) {
                    initialAlerts.forEach(item => this.pushAlert(item));
                }

                // Global JS Bridge
                window.devfolioAlert = (data) => {
                    this.pushAlert(data);
                };

                // Listen to window custom events
                window.addEventListener('devfolio-alert', (e) => {
                    this.pushAlert(e.detail);
                });

                window.addEventListener('notify', (e) => {
                    this.pushAlert(e.detail);
                });

                window.addEventListener('alert', (e) => {
                    this.pushAlert(e.detail);
                });

                window.addEventListener('task-completed', (e) => {
                    const payload = typeof e.detail === 'string' ? { message: e.detail, type: 'success' } : e.detail;
                    this.pushAlert(payload);
                });

                // Listen to Livewire event dispatches
                if (window.Livewire) {
                    window.Livewire.on('notify', (data) => {
                        const payload = Array.isArray(data) ? data[0] : data;
                        this.pushAlert(payload);
                    });

                    window.Livewire.on('alert', (data) => {
                        const payload = Array.isArray(data) ? data[0] : data;
                        this.pushAlert(payload);
                    });

                    window.Livewire.on('task-completed', (data) => {
                        const payload = Array.isArray(data) ? data[0] : data;
                        const formatted = typeof payload === 'string' ? { message: payload, type: 'success' } : payload;
                        this.pushAlert(formatted);
                    });
                }

                // Handle Livewire SPA navigations
                document.addEventListener('livewire:navigated', () => {
                    // re-bind if necessary
                });
            },

            pushAlert(payload) {
                if (!payload) return;

                let message = '';
                let type = 'info';
                let title = null;
                let duration = 4500; // 4.5 seconds

                if (typeof payload === 'string') {
                    message = payload;
                } else if (typeof payload === 'object') {
                    message = payload.message || payload.text || payload.body || 'Task completed';
                    type = payload.type || payload.status || 'info';
                    title = payload.title || null;
                    if (payload.duration) duration = payload.duration;
                }

                if (!message) return;

                const alertId = 'pill_' + Math.random().toString(36).substr(2, 9) + Date.now();
                const alertItem = {
                    id: alertId,
                    message: message,
                    type: type,
                    title: title,
                    visible: true,
                    duration: duration,
                    remaining: duration,
                    progress: 100,
                    paused: false,
                    timer: null,
                    interval: null,
                };

                this.alerts.push(alertItem);
                this.startTimer(alertItem);
            },

            startTimer(alert) {
                const step = 50;
                alert.interval = setInterval(() => {
                    if (!alert.paused) {
                        alert.remaining -= step;
                        alert.progress = Math.max(0, (alert.remaining / alert.duration) * 100);

                        if (alert.remaining <= 0) {
                            clearInterval(alert.interval);
                            this.removeAlert(alert.id);
                        }
                    }
                }, step);
            },

            pauseAlert(id) {
                const alert = this.alerts.find(a => a.id === id);
                if (alert) {
                    alert.paused = true;
                }
            },

            resumeAlert(id) {
                const alert = this.alerts.find(a => a.id === id);
                if (alert) {
                    alert.paused = false;
                }
            },

            removeAlert(id) {
                const alertIndex = this.alerts.findIndex(a => a.id === id);
                if (alertIndex !== -1) {
                    const alert = this.alerts[alertIndex];
                    if (alert.interval) clearInterval(alert.interval);
                    alert.visible = false;
                    setTimeout(() => {
                        this.alerts = this.alerts.filter(a => a.id !== id);
                    }, 250);
                }
            }
        }));
    });
</script>
