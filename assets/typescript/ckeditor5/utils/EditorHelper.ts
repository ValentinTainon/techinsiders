export class EditorHelper {
  static redirectToPageIndex(): void {
    window.location.replace(
      window.location.href.split("/").slice(0, 6).join("/")
    );
  }
}
