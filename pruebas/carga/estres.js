// Prueba de carga de la API de Alcanza con k6.
// Sube de 1 a 50 usuarios virtuales y mide tiempos y errores en las rutas que más se usan.
import http from 'k6/http'
import { check, sleep } from 'k6'
import { Trend } from 'k6/metrics'

const BASE = __ENV.BASE || 'http://laravel.test/api'
const listar = new Trend('t_listar_metas', true)
const perfil = new Trend('t_perfil', true)
const abonar = new Trend('t_abonar', true)

export const options = {
  scenarios: __ENV.VUS ? { fijo: { executor: 'constant-vus', vus: Number(__ENV.VUS), duration: '40s' } } : {
    rampa: {
      executor: 'ramping-vus',
      startVUs: 1,
      stages: [
        { duration: '20s', target: 10 },
        { duration: '30s', target: 25 },
        { duration: '30s', target: 50 },
        { duration: '30s', target: 50 },
        { duration: '10s', target: 0 },
      ],
    },
  },
  thresholds: {
    http_req_failed: ['rate<0.01'],
    http_req_duration: ['p(95)<800'],
  },
  summaryTrendStats: ['avg', 'med', 'p(90)', 'p(95)', 'max'],
}

/** Crea una cuenta de prueba con perfil y una meta; corre una sola vez antes de la carga. */
export function setup() {
  const h = { 'Content-Type': 'application/json', Accept: 'application/json' }
  const email = `estres-${Date.now()}@alcanza.test`
  const r = http.post(`${BASE}/registro`, JSON.stringify({ nombre: 'Estres', email, password: 'Prueba1234x', password_confirmation: 'Prueba1234x', turnstile: 'XXXX.DUMMY.TOKEN.XXXX' }), { headers: h })
  const token = r.json('token')
  const auth = { ...h, Authorization: `Bearer ${token}` }
  http.post(`${BASE}/perfil`, JSON.stringify({ nombre: 'Estres', ahorro_mensual: 400000 }), { headers: auth })
  const metas = []
  for (let i = 0; i < 5; i++) {
    const m = http.post(`${BASE}/metas`, JSON.stringify({ producto_id: `${i + 1}`, nombre_producto: `Producto ${i + 1}`, precio: 900000000, moneda: 'COP', descuento: 0 }), { headers: auth })
    metas.push(m.json('id'))
  }
  return { auth, metas }
}

export default function ({ auth, metas }) {
  const r1 = http.get(`${BASE}/metas`, { headers: auth, tags: { ruta: 'GET /metas' } })
  listar.add(r1.timings.duration)
  check(r1, { 'metas 200': (r) => r.status === 200 })

  const r2 = http.get(`${BASE}/perfil`, { headers: auth, tags: { ruta: 'GET /perfil' } })
  perfil.add(r2.timings.duration)
  check(r2, { 'perfil 200': (r) => r.status === 200 })

  const meta = metas[Math.floor(Math.random() * metas.length)]
  const r3 = http.post(`${BASE}/metas/${meta}/abonos`, JSON.stringify({ monto: 1000 }), { headers: auth, tags: { ruta: 'POST abonos' } })
  abonar.add(r3.timings.duration)
  check(r3, { 'abono 201': (r) => r.status === 201 })
  if (r3.status !== 201 && __ITER < 3) console.log(`abono ${r3.status}: ${String(r3.body).slice(0, 160)}`)

  sleep(1)
}
