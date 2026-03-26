import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import Text from "@/Pages/Components/FiledTypes/Text.vue";
import Email from "@/Pages/Components/FiledTypes/Email.vue";
import LongText from "@/Pages/Components/FiledTypes/LongText.vue";
import DateTime from "@/Pages/Components/FiledTypes/DateTime.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";

const defaultValidate = () => true;

const emailValidate = (value) => {
  if (!value) return true;

  const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
  return emailRegex.test(value.toString());
};

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
    component: Select,
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
};
