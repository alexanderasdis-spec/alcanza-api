// Simula un abuso: una sola cuenta manda peticiones sin parar mientras otra persona usa la app normal.
// Mide si el atacante queda frenado (429) y si la persona normal sigue bien atendida.
import http from 'k6/http'
import { check, sleep } from 'k6'
import { Counter, Trend } from 'k6/metrics'

const BASE = __ENV.BASE || 'http://laravel.test/api'
const frenadas = new Counter('atacante_frenadas_429')
const atendidas = new Counter('atacante_atendidas_200')
const tNormal = new Trend('t_usuario_normal', true)

export const options = {
  scenarios: {
    atacante: { executor: 'constant-vus', vus: 30, duration: '40s', exec: 'atacar' },
    normal: { executor: 'constant-vus', vus: 1, duration: '40s', exec: 'usarNormal' },
  },
  summaryTrendStats: ['avg', 'med', 'p(95)', 'max'],
}

/** Crea dos cuentas con perfil: la del atacante y la de una persona normal. */
export function setup() {
  const h = { 'Content-Type': 'application/json', Accept: 'application/json' }
  const cuenta = (nombre) => {
    const r = http.post(`${BASE}/registro`, JSON.stringify({ nombre, email: `${nombre.toLowerCase()}-${Date.now()}@alcanza.test`, password: 'Prueba1234x', password_confirmation: 'Prueba1234x', turnstile: 'XXXX.DUMMY.TOKEN.XXXX' }), { headers: h })
    const auth = { ...h, Authorization: `Bearer ${r.json('token')}` }
    http.post(`${BASE}/perfil`, JSON.stringify({ nombre, ahorro_mensual: 400000 }), { headers: auth })
    return auth
  }
  return { atacante: cuenta('Atacante'), normal: cuenta('Normal') }
}

/** El atacante no espera entre peticiones. */
export function atacar({ atacante }) {
  const r = http.get(`${BASE}/metas`, { headers: atacante, tags: { quien: 'atacante' } })
  if (r.status === 429) frenadas.add(1)
  else if (r.status === 200) atendidas.add(1)
}

/** La persona normal hace una consulta por segundo. */
export function usarNormal({ normal }) {
  const r = http.get(`${BASE}/perfil`, { headers: normal, tags: { quien: 'normal' } })
  tNormal.add(r.timings.duration)
  check(r, { 'usuario normal atendido (200)': (x) => x.status === 200 })
  sleep(1)
}
