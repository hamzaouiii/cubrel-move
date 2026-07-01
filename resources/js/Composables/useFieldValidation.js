import { usePage } from "@inertiajs/vue3";
import { fieldRegistry } from "@/Registries/fieldRegistry";

export function useFieldValidation(props) {
  const getFieldType = (fieldName) => {
    return props.fields?.find((f) => f.name === fieldName)?.type || null;
  };

  const validateFieldTypes = (payload) => {
    const errors = [];
    const locale = usePage().props.locale;

    Object.keys(payload).forEach((key) => {
      const fieldType = getFieldType(key);
      const fieldDef = fieldRegistry[fieldType];
      if (!fieldDef || !fieldDef.validate) return;
      const isValid = fieldDef.validate(payload[key], locale);

      if (!isValid) {
        const fieldMeta = props.fields.find((f) => f.name === key);

        errors.push({
          field: key,
          label: fieldMeta?.label || key,
          type: "invalid",
        });
      }
    });

    return errors;
  };

  return {
    validateFieldTypes,
  };
}
