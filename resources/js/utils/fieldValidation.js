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

export function fieldValidation() {
  return {
    emailValidate,
    phoneValidate,
    urlValidate,
    defaultValidate,
    urlValidateSimple,
    percentageValidate,
  };
}
