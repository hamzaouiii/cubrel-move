import Checkbox from "@/Pages/Components/FiledTypes/Checkbox.vue";
import Text from "@/Pages/Components/FiledTypes/Text.vue";
import LongText from "@/Pages/Components/FiledTypes/LongText.vue";
import DateTime from "@/Pages/Components/FiledTypes/DateTime.vue";
import Select from "@/Pages/Components/FiledTypes/Select.vue";
import Selectbox from "@/Pages/Components/FiledTypes/Selectbox.vue";
import Radiobox from "@/Pages/Components/FiledTypes/Radiobox.vue";
import Switcher from "@/Pages/Components/FiledTypes/Switcher.vue";

export const fieldRegistry = {
  text: Text,
  longtext: LongText,
  boolean: Checkbox,
  checkbox: Checkbox,
  select: Select,
  dropdown: Select,
  date: DateTime,
  datetime: DateTime,
};
