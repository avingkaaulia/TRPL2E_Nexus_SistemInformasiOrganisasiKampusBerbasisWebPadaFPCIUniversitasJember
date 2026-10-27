import http from 'k6/http';
import { sleep } from 'k6';

export const options = {
  vus: 40,
  duration: '30s',
};

export default function () {
  http.get('https://fpci-unej.portalweb.my.id/');
  sleep(1);
}