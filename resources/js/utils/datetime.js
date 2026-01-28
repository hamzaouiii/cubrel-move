import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import localizedFormat from "dayjs/plugin/localizedFormat";

import "dayjs/locale/en";
import "dayjs/locale/de";

dayjs.extend(utc);
dayjs.extend(timezone);
dayjs.extend(localizedFormat);

const PHP_TO_DAYJS = {
  "Y-m-d H:i": "YYYY-MM-DD HH:mm",
  "d.m.Y H:i": "DD.MM.YYYY HH:mm",
  "d/m/Y H:i": "DD/MM/YYYY HH:mm",
  "m/d/Y h:i A": "MM/DD/YYYY hh:mm A",
  "M d, Y H:i": "MMM DD, YYYY HH:mm",
  "D, d M Y H:i": "ddd, DD MMM YYYY HH:mm",

  "Y-m-d": "YYYY-MM-DD",
  "d.m.Y": "DD.MM.YYYY",
  "d/m/Y": "DD/MM/YYYY",

  "d.m.Y H:i:s": "DD.MM.YYYY HH:mm:ss",
  "d.m.y H:i": "DD.MM.YY HH:mm",
  "d.m.Y, H:i": "DD.MM.YYYY, HH:mm",
  "d.m.Y - H:i": "DD.MM.YYYY - HH:mm",

  "D, d.m.Y H:i": "ddd, DD.MM.YYYY HH:mm",
  "l, d.m.Y H:i": "dddd, DD.MM.YYYY HH:mm",

  "Y-m-d\\TH:i": "YYYY-MM-DDTHH:mm",

  "d.m.y": "DD.MM.YY",
  "d. m. Y": "DD. MM. YYYY",

  "D, d.m.Y": "ddd, DD.MM.YYYY",
  "l, d.m.Y": "dddd, DD.MM.YYYY",

  "d. M Y": "DD. MMM YYYY",
  "d. F Y": "DD. MMMM YYYY",
};

function isDateOnly(value) {
  return typeof value === "string" && /^\d{4}-\d{2}-\d{2}$/.test(value);
}

function normalizeLocale(appLocale) {
  if (!appLocale) return "en";
  return String(appLocale).replace("_", "-").split("-")[0].toLowerCase();
}

export function formatDateTime(value, settings) {
  if (!value) return "";

  const tz = settings?.timezone || "UTC";
  const phpFmt = settings?.datetime_format || "Y-m-d H:i";
  const fmt = PHP_TO_DAYJS[phpFmt] || "YYYY-MM-DD HH:mm";

  const locale = normalizeLocale(settings?.app_locale);

  if (isDateOnly(value)) {
    return dayjs(value).locale(locale).format(fmt);
  }

  return dayjs(value).tz(tz).locale(locale).format(fmt);
}
