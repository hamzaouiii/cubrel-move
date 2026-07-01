import { isPossiblePhoneNumber } from "libphonenumber-js";

const defaultValidate = () => true; // no validation all values are accepted

// syntax email format validation
const emailValidate = (value) => {
  if (!value) return true;

  const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
  return emailRegex.test(value.toString());
};

// Maps the app locale (config/app.php: available_locales) to a default
// ISO country code for phone number parsing. Falls back to DE.
const localeToCountryCode = {
  de: "DE",
  en: "GB",
};

// Lenient phone check: only rejects strings that can't plausibly be a
// phone number (wrong characters, wrong length), not ones that merely
// don't match the assumed country's national dialing plan. The locale
// only supplies a default country for numbers with no country code -
// numbers with their own country code (e.g. "+1 555 ...") are judged
// on their own terms, so foreign numbers aren't penalized.
const phoneValidate = (value, locale) => {
  if (!value) return true;
  const countryCode = localeToCountryCode[locale] || "DE";
  return isPossiblePhoneNumber(String(value), countryCode);
};

//url format validation
//enforces https urls and matches protocol
const urlValidate = (value) => {
  if (!value) return true;

  const urlRegex =
    /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i;

  try {
    const url = value.toString();

    const urlWithProtocol = url.match(/^https?:\/\//i) ? url : `https://${url}`;
    new URL(urlWithProtocol);

    return urlRegex.test(url);
  } catch {
    return false;
  }
};

// simpler version for format validation only
const urlValidateSimple = (value) => {
  if (!value) return true;

  const urlRegex =
    /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i;
  return urlRegex.test(value.toString());
};

// format validation for percentage fields
const percentageValidate = (value) => {
  if (value === null || value === "" || value === undefined) return true;

  const num = parseFloat(value);

  if (isNaN(num)) return false;

  if (num < 0 || num > 100) return false;

  return true;
};

// Integer validation
const integerValidate = (value) => {
  if (value === null || value === "" || value === undefined) return true;

  const num = parseInt(value);

  // Check if it's a valid integer
  if (isNaN(num)) return false;

  // Check if it's an integer (no decimal places)
  if (num !== parseFloat(value)) return false;

  return true;
};

// Integer validation with min/max
const integerValidateWithRange = (value, min = null, max = null) => {
  if (value === null || value === "" || value === undefined) return true;

  const num = parseInt(value);

  if (isNaN(num)) return false;
  if (num !== parseFloat(value)) return false;
  if (min !== null && num < min) return false;
  if (max !== null && num > max) return false;

  return true;
};

// Decimal validation
const decimalValidate = (value) => {
  if (value === null || value === "" || value === undefined) return true;

  const num = parseFloat(value);

  // Check if it's a valid number
  if (isNaN(num)) return false;

  return true;
};

// Decimal validation with precision
const decimalValidateWithPrecision = (
  value,
  precision = 2,
  min = null,
  max = null,
) => {
  if (value === null || value === "" || value === undefined) return true;

  const num = parseFloat(value);

  if (isNaN(num)) return false;

  // Check precision (decimal places)
  const decimalPlaces = (value.toString().split(".")[1] || "").length;
  if (decimalPlaces > precision) return false;

  if (min !== null && num < min) return false;
  if (max !== null && num > max) return false;

  return true;
};

const relatedValidate = (value) => {
  if (!value) return true;

  // Validate that the value is a valid UUID (any version)
  const uuidRegex =
    /^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i;
  return uuidRegex.test(value);
};

// Address: valid when all present sub-values are non-empty strings
const addressValidate = (value) => {
  if (!value) return true;
  return Object.values(value).every(
    (v) => v === null || v === undefined || v === "" || typeof v === "string",
  );
};

const currencyValidate = (value) => {
  if (value === null || value === "" || value === undefined) return true;
  const num = parseFloat(value);
  return !isNaN(num) && num >= 0;
};

// Image: value is the uploaded file's URL/path, set by ImageField after a successful upload
const imageValidate = (value) => {
  if (!value) return true;
  return typeof value === "string";
};

export function fieldValidation() {
  return {
    emailValidate,
    phoneValidate,
    urlValidate,
    defaultValidate,
    urlValidateSimple,
    percentageValidate,
    integerValidate,
    decimalValidate,
    relatedValidate,
    addressValidate,
    currencyValidate,
    imageValidate,
  };
}
