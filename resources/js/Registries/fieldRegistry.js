import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import Text from "@/Pages/Components/FiledTypes/Text.vue";
import Email from "@/Pages/Components/FiledTypes/Email.vue";
import LongText from "@/Pages/Components/FiledTypes/LongText.vue";
import DateTime from "@/Pages/Components/FiledTypes/DateTime.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import PhoneField from "@/Pages/Components/FiledTypes/PhoneField.vue";
import UrlField from "@/Pages/Components/FiledTypes/UrlField.vue";
import PercentageField from "@/Pages/Components/FiledTypes/PercentageField.vue";
import IntegerField from "@/Pages/Components/FiledTypes/IntegerField.vue";
import DecimalField from "@/Pages/Components/FiledTypes/DecimalField.vue";
import RelatedRecord from "@/Pages/Components/FiledTypes/RelatedRecord.vue";
import StatusField from "@/Pages/Components/FiledTypes/StatusField.vue";
import AddressField from "@/Pages/Components/FiledTypes/AddressField.vue";
import CurrencyField from "@/Pages/Components/FiledTypes/CurrencyField.vue";

import { fieldValidation } from "@/utils/fieldValidation";
const {
  defaultValidate,
  emailValidate,
  urlValidate,
  phoneValidate,
  percentageValidate,
  decimalValidate,
  integerValidate,
  relatedValidate,
  addressValidate,
  currencyValidate,
} = fieldValidation();

export const fieldRegistry = {
  text: {
    component: Text,
    validate: defaultValidate,
  },
  longtext: {
    component: LongText,
    validate: defaultValidate,
  },
  boolean: {
    component: Checkbox,
    validate: defaultValidate,
  },
  checkbox: {
    component: Checkbox,
    validate: defaultValidate,
  },
  select: {
    component: Select,
    validate: defaultValidate,
  },
  dropdown: {
    component: StatusField,
    validate: defaultValidate,
  },
  date: {
    component: DateTime,
    validate: defaultValidate,
  },
  datetime: {
    component: DateTime,
    validate: defaultValidate,
  },
  email: {
    component: Email,
    validate: emailValidate,
  },
  phone: {
    component: PhoneField,
    validate: phoneValidate,
  },
  url: {
    component: UrlField,
    validate: urlValidate,
  },
  percentage: {
    component: PercentageField,
    validate: percentageValidate,
  },
  integer: {
    component: IntegerField,
    validate: integerValidate,
  },
  decimal: {
    component: DecimalField,
    validate: decimalValidate,
  },
  record: {
    component: RelatedRecord,
    validate: relatedValidate,
  },
  status: {
    component: StatusField,
    validate: defaultValidate,
  },
  address: {
    component: AddressField,
    validate: addressValidate,
    isComposite: true,
  },
  currency: {
    component: CurrencyField,
    validate: currencyValidate,
  },
};
