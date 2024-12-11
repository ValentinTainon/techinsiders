export default class EditorMediaCleaner {
  private pageName: string;
  private eventType: string;
  private postUuid: string;
  private form: HTMLFormElement | null;
  private editorInput: HTMLDivElement | null;
  private imgPathsOnLoad: (string | null)[];

  constructor(pageName: string, postUuid: string) {
    this.pageName = pageName;
    this.postUuid = postUuid;
    this.form = document.querySelector<HTMLFormElement>(
      "form#new-Post-form, form#edit-Post-form"
    );
    this.editorInput = document.querySelector<HTMLDivElement>(
      "div.ck-editor__editable"
    );
  }

  public cleanUnusedImages(): void {
    if (!this.pageName || !this.postUuid || !this.form || !this.editorInput) {
      throw new Error(
        "Cannot initialize EditorImageCleaner due to missing page name, post UUID, form, or editor input."
      );
    }

    let isFormSubmitted = false;

    if (this.pageName === "edit") {
      window.addEventListener("load", () => {
        this.imgPathsOnLoad = this.getAllImgPaths();
      });
    }

    this.form?.addEventListener("submit", (event: SubmitEvent) => {
      event.preventDefault();
      this.eventType = event.type;
      this.cleanUnusedImagesOnSubmit(
        this.createRequestPayload(this.getAllImgPaths())
      );
      this.form?.submit();
      isFormSubmitted = true;
    });

    window.addEventListener("beforeunload", (event: BeforeUnloadEvent) => {
      if (isFormSubmitted) return;

      this.eventType = event.type;

      if (this.pageName === "new") {
        this.cleanUnusedImagesBeforeUnload(this.createRequestPayload());
      }

      if (this.pageName === "edit") {
        this.cleanUnusedImagesBeforeUnload(
          this.createRequestPayload(this.imgPathsOnLoad)
        );
      }
    });
  }

  private getAllImgPaths(): (string | null)[] {
    return Array.from(
      this.editorInput?.querySelectorAll<HTMLImageElement>("img") ?? []
    ).map((image) => `public${image.getAttribute("src")}`);
  }

  private createRequestPayload(postImgPaths: (string | null)[] = []): string {
    return JSON.stringify({
      pageName: this.pageName,
      eventType: this.eventType,
      postUuid: this.postUuid,
      postImgPaths: postImgPaths,
    });
  }

  private async cleanUnusedImagesOnSubmit(payload: string): Promise<void> {
    try {
      await fetch("/handle-deleted-post-images", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: payload,
      });
    } catch (error) {
      console.error("Error when cleaning unused images on submit: ", error);
    }
  }

  private cleanUnusedImagesBeforeUnload(payload: string): void {
    try {
      const data = new Blob([payload], {
        type: "application/json",
      });
      navigator.sendBeacon("/handle-deleted-post-images", data);
    } catch (error: any) {
      console.error("Error when cleaning unused images before unload: ", error);
    }
  }
}
