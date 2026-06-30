import TimeSeriesWidget from "./widgets/TimeSeriesWidget.vue";
import TimeSeriesConfigForm from "./TimeSeriesConfigForm.vue";
import MetricWidget from "./widgets/MetricWidget.vue";
import MetricConfigForm from "./MetricConfigForm.vue";
import BreakdownWidget from "./widgets/BreakdownWidget.vue";
import BreakdownConfigForm from "./BreakdownConfigForm.vue";
import RecordListWidget from "./widgets/RecordListWidget.vue";
import RecordListConfigForm from "./RecordListConfigForm.vue";
import MyRecords from "./MyRecords.vue";

export const WIDGET_REGISTRY = {
  "my-records": {
    label: "My Records",
    description: "Records assigned to you across all modules",
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
    label: "Metric",
    description: "A single count, sum, or average for any module",
    icon: "fa-solid fa-hashtag",
    defaultCols: 1,
    component: MetricWidget,
    configComponent: MetricConfigForm,
  },
  "time-series": {
    label: "Records over time",
    description: "Line or bar chart of any module field over time",
    icon: "fa-solid fa-chart-line",
    defaultCols: 4,
    component: TimeSeriesWidget,
    configComponent: TimeSeriesConfigForm,
  },
  breakdown: {
    label: "Breakdown",
    description: "Donut or bar chart grouped by any field",
    icon: "fa-solid fa-chart-pie",
    defaultCols: 2,
    component: BreakdownWidget,
    configComponent: BreakdownConfigForm,
  },
  "record-list": {
    label: "Record list",
    description: "A compact table of records from any module",
    icon: "fa-solid fa-table-list",
    defaultCols: 1,
    component: RecordListWidget,
    configComponent: RecordListConfigForm,
  },
};
