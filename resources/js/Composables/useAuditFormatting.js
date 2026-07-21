import { computed, getCurrentInstance } from "vue";
import { usePage } from "@inertiajs/vue3";
import { formatDate, formatDateTime } from "@/utils/datetime";

export function useAuditFormatting(props) {
  const { proxy } = getCurrentInstance();
  const t = proxy.$t;
  const appSettings = usePage().props.appSettings;
  const allModules = computed(() => usePage().props.modules ?? []);

  const fieldDef = (name) => props.fields?.find((f) => f.name === name);

  const fieldLabel = (name) => {
    const field = fieldDef(name);
    return field?.label ? t(field.label) : name;
  };

  const dropdownOption = (name, rawValue) => {
    const values = fieldDef(name)?.dropdown_list?.values;
    if (!values) return null;
    return values.find((v) => String(v.value) === String(rawValue)) ?? null;
  };

  const dropdownLabel = (name, rawValue) => {
    const option = dropdownOption(name, rawValue);
    return option ? t(option.label) : null;
  };

  const formatValue = (name, value) => {
    if (value === null || value === undefined || value === "") {
      return t("globals.audit_trail.empty_value");
    }
    const type = fieldDef(name)?.type;
    if (type === "date") return formatDate(value, appSettings);
    if (type === "datetime") return formatDateTime(value, appSettings);
    if (["bool", "boolean", "checkbox"].includes(type)) {
      return value ? t("globals.audit_trail.yes") : t("globals.audit_trail.no");
    }
    if (["select", "dropdown", "status"].includes(type)) {
      return dropdownLabel(name, value) ?? String(value);
    }
    return String(value);
  };

  const when = (value) => formatDateTime(value, appSettings);

  // 'record' type fields  carry a resolved *_label alongside

  const diffValue = (name, diff, which) => {
    const label = diff[`${which}_label`];
    if (label !== undefined) {
      return label ?? t("globals.audit_trail.empty_value");
    }
    return formatValue(name, diff[which]);
  };

  const isBulkChange = (changes) => !!changes && changes.count !== undefined;

  const hasRecordOldValue = (changes) =>
    !!changes && changes.old_value !== undefined;

  const bulkSummary = (entry) => {
    const c = entry.changes;
    return t("globals.audit_trail.bulk_summary_detail", {
      count: c.count,
      field: fieldLabel(c.field),
      value: formatValue(c.field, c.value),
    });
  };

  const isLinkChange = (entry) =>
    entry.action === "linked" || entry.action === "unlinked";

  const relatedModuleLabel = (slug) =>
    allModules.value.find((m) => m.slug === slug)?.label ?? slug;

  const relatedModuleSingleLabel = (slug) => {
    const module = allModules.value.find((m) => m.slug === slug);
    return module?.single_label ?? module?.label ?? slug;
  };

  const linkSummary = (entry) => {
    const c = entry.changes;
    return t("globals.audit_trail.link_summary", {
      related: c.related_label ?? c.related_id,
      module: relatedModuleLabel(c.related_module),
    });
  };

  const isStatusField = (name) => fieldDef(name)?.type === "status";

  return {
    fieldLabel,
    dropdownLabel,
    dropdownOption,
    isStatusField,
    formatValue,
    when,
    diffValue,
    isBulkChange,
    hasRecordOldValue,
    bulkSummary,
    isLinkChange,
    relatedModuleLabel,
    relatedModuleSingleLabel,
    linkSummary,
  };
}
