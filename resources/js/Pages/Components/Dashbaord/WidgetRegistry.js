import TimeSeriesWidget from "./widgets/TimeSeriesWidget.vue";
import TimeSeriesConfigForm from "./TimeSeriesConfigForm.vue";
import MetricWidget from "./widgets/MetricWidget.vue";
import MetricConfigForm from "./MetricConfigForm.vue";
import BreakdownWidget from "./widgets/BreakdownWidget.vue";
import BreakdownConfigForm from "./BreakdownConfigForm.vue";
import RecordListWidget from "./widgets/RecordListWidget.vue";
import RecordListConfigForm from "./RecordListConfigForm.vue";
import PeopleWidget from "./widgets/PeopleWidget.vue";
import PeopleConfigForm from "./PeopleConfigForm.vue";
import MyRecords from "./MyRecords.vue";

export const WIDGET_REGISTRY = {
  "my-records": {
    label: "globals.dashboard.widget_my_records_label",
    description: "globals.dashboard.widget_my_records_desc",
    icon: "fa-regular fa-folder-open",
    cols: 1,
    component: MyRecords,
    getProps: (p) => ({ ownedRecords: p.ownedRecords }),
  },
};

/**
 * Configurable widget types — each has a runtime component and a config form.
 * Instances are stored as { instanceId, type, cols, config } objects in the layout.
 */
export const WIDGET_TYPES = {
  metric: {
    label: "globals.dashboard.widget_metric_label",
    description: "globals.dashboard.widget_metric_desc",
    icon: "fa-solid fa-hashtag",
    defaultCols: 1,
    component: MetricWidget,
    configComponent: MetricConfigForm,
  },
  "time-series": {
    label: "globals.dashboard.widget_time_series_label",
    description: "globals.dashboard.widget_time_series_desc",
    icon: "fa-solid fa-chart-line",
    defaultCols: 4,
    component: TimeSeriesWidget,
    configComponent: TimeSeriesConfigForm,
  },
  breakdown: {
    label: "globals.dashboard.widget_breakdown_label",
    description: "globals.dashboard.widget_breakdown_desc",
    icon: "fa-solid fa-chart-pie",
    defaultCols: 2,
    component: BreakdownWidget,
    configComponent: BreakdownConfigForm,
  },
  "record-list": {
    label: "globals.dashboard.widget_record_list_label",
    description: "globals.dashboard.widget_record_list_desc",
    icon: "fa-solid fa-table-list",
    defaultCols: 1,
    component: RecordListWidget,
    configComponent: RecordListConfigForm,
  },
  people: {
    label: "globals.dashboard.widget_people_label",
    description: "globals.dashboard.widget_people_desc",
    icon: "fa-solid fa-ranking-star",
    defaultCols: 2,
    component: PeopleWidget,
    configComponent: PeopleConfigForm,
  },
};
