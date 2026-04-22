import { isValidPhoneNumber } from "libphonenumber-js";

const defaultValidate = () => true;

const emailValidate = (value) => {
  if (!value) return true;

  const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
  return emailRegex.test(value.toString());
};

const phoneValidate = (value) => {
  return isValidPhoneNumber(String(value), "DE");
};

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

const urlValidateSimple = (value) => {
  if (!value) return true;

  const urlRegex =
    /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i;
  return urlRegex.test(value.toString());
};

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

  // Validate that the value is a valid ID (positive integer)
  const id = parseInt(value);
  if (isNaN(id)) return false;
  if (id <= 0) return false;

  return true;
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
  };
}
