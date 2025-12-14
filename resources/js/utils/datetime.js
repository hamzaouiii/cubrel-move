import dayjs from "dayjs";
import utc from "dayjs/plugin/utc";
import timezone from "dayjs/plugin/timezone";
import localizedFormat from "dayjs/plugin/localizedFormat";

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
};

function isDateOnly(value) {
  return typeof value === "string" && /^\d{4}-\d{2}-\d{2}$/.test(value);
}

export function formatDateTime(value, settings) {
  if (!value) return "";

  const tz = settings?.timezone || "UTC";
  const phpFmt = settings?.datetime_format || "Y-m-d H:i";
  const fmt = PHP_TO_DAYJS[phpFmt] || "YYYY-MM-DD HH:mm";

  if (isDateOnly(value)) {
    return dayjs(value).format(fmt);
  }

  return dayjs(value).tz(tz).format(fmt);
}
