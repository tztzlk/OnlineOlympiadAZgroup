import http from 'k6/http';
import { check, sleep } from 'k6';

// Замени на свой домен
const BASE_URL = 'https://eurica.kz';

// Замени на реальный токен залогиненного пользователя
// Получить: DevTools → Network → любой /api/ запрос → заголовок Authorization
const TOKEN = 'Bearer ВСТАВЬ_ТОКЕН_СЮДА';

export const options = {
  stages: [
    { duration: '30s', target: 100 },   // разгон до 100 пользователей
    { duration: '1m',  target: 500 },   // нагрузка 500
    { duration: '30s', target: 1000 },  // пик 1000
    { duration: '30s', target: 0 },     // спад
  ],
  thresholds: {
    http_req_duration: ['p(95)<2000'],  // 95% запросов < 2 сек
    http_req_failed:   ['rate<0.01'],   // ошибок < 1%
  },
};

export default function () {
  // Публичная главная (без токена)
  const home = http.get(`${BASE_URL}/`);
  check(home, { 'home 200': (r) => r.status === 200 });

  // Профиль (требует токен)
  const profile = http.get(`${BASE_URL}/api/profile/me`, {
    headers: { Authorization: TOKEN },
  });
  check(profile, {
    'profile 200': (r) => r.status === 200,
    'profile <500ms': (r) => r.timings.duration < 500,
  });

  sleep(1);
}
