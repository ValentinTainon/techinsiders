// @ts-ignore
import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect(): void {
    const url: string = window.location.pathname + "/view-increment";

    this.incrementView(url);
  }

  private async incrementView(url: string): Promise<void> {
    try {
      await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
      });
    } catch (error) {
      console.error("Error while incrementing view: ", error);
    }
  }
}
