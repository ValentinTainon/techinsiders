import { TableConfig } from "./types/TableConfig";

export class TableProperty {
  static getConfig(): TableConfig {
    return {
      table: {
        contentToolbar: [
          "tableColumn",
          "tableRow",
          "mergeTableCells",
          "tableCellProperties",
          "tableProperties",
        ],
      },
    };
  }
}
