export default class ViewIncrementor {
  public async incrementView(): Promise<void> {
    const url = window.location.pathname + "/view-increment";

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

document.addEventListener("DOMContentLoaded", async () => {
  new ViewIncrementor().incrementView();
});
