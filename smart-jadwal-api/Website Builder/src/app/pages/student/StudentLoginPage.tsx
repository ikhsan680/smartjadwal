import { useState } from "react";
import { useNavigate } from "react-router-dom";
import { GraduationCap } from "lucide-react";

export function StudentLoginPage() {
  const [studentName, setStudentName] = useState("");
  const [selectedClass, setSelectedClass] = useState("");
  const navigate = useNavigate();

  const classes = [
    "X RPL 1",
    "X RPL 2",
    "XI RPL 1",
    "XI RPL 2",
    "XII RPL 1",
    "XII RPL 2",
    "X TKJ 1",
    "XI TKJ 1"
  ];

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (studentName && selectedClass) {
      // Store student info in sessionStorage
      sessionStorage.setItem("studentName", studentName);
      sessionStorage.setItem("studentClass", selectedClass);
      navigate("/siswa/dashboard");
    }
  };

  return (
    <div className="min-h-screen bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center p-4">
      <div className="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div className="flex flex-col items-center mb-8">
          <div className="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mb-4">
            <GraduationCap className="w-10 h-10 text-white" />
          </div>
          <h1 className="text-3xl text-gray-900 text-center">Smart Jadwal</h1>
          <p className="text-gray-600 text-center mt-2"> Portal</p>
        </div>

        <form onSubmit={handleSubmit} className="space-y-6">
          <div>
            <label htmlFor="studentName" className="block text-sm mb-2 text-gray-700">Nama Siswa
            </label>
            <input
              type="text"
              id="studentName"
              value={studentName}
              onChange={(e) => setStudentName(e.target.value)}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none"
              placeholder="Masukan Nama Anda"
              required
            />
          </div>

          <div>
            <label htmlFor="class" className="block text-sm mb-2 text-gray-700">
              Pilih kelas
            </label>
            <select
              id="class"
              value={selectedClass}
              onChange={(e) => setSelectedClass(e.target.value)}
              className="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none"
              required
            >
              <option value="">Pilih kelas</option>
              {classes.map((cls) => (
                <option key={cls} value={cls}>{cls}</option>
              ))}
            </select>
          </div>

          <button
            type="submit"
            className="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors"
          >
            Login
          </button>
        </form>

        <div className="mt-6 text-center">
          <a href="/guru" className="text-sm text-blue-600 hover:text-blue-700">
            Teacher/Admin Portal →
          </a>
        </div>
      </div>
    </div>
  );
}
