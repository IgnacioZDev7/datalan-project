import api from "../services/api";

export async function downloadReport(endpoint: string, filename: string, params?: Record<string, string>) {
  const res = await api.get(endpoint, { responseType: "blob", params });
  const blob = new Blob([res.data]);
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = filename;
  link.click();
  URL.revokeObjectURL(link.href);
}
