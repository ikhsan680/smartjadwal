import { User, LogOut, Calendar, BookOpen } from "lucide-react";
import { useNavigate, NavLink } from "react-router-dom";
import { GraduationCap } from "lucide-react";

export function StudentTopNav() {
  const navigate = useNavigate();
  const studentName = sessionStorage.getItem("studentName") || "Student";
  const studentClass = sessionStorage.getItem("studentClass") || "";

  const handleLogout = () => {
    sessionStorage.removeItem("studentName");
    sessionStorage.removeItem("studentClass");
    navigate("/siswa");
  };

  return (
    <header className="bg-white border-b border-gray-200 sticky top-0 z-10">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex items-center justify-between h-16">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
              <GraduationCap className="w-6 h-6 text-white" />
            </div>
            <div>
              <h2 className="text-lg text-gray-900">Smart Jadwal</h2>
              <p className="text-xs text-gray-500">Student Portal</p>
            </div>
          </div>

          <nav className="hidden md:flex items-center gap-2">
            <NavLink
              to="/siswa/dashboard"
              className={({ isActive }) =>
                `flex items-center gap-2 px-4 py-2 rounded-lg transition-colors ${
                  isActive
                    ? "bg-blue-50 text-blue-600"
                    : "text-gray-700 hover:bg-gray-50"
                }`
              }
            >
              <Calendar className="w-4 h-4" />
              <span className="text-sm">Dashboard</span>
            </NavLink>
            <NavLink
              to="/siswa/schedule"
              className={({ isActive }) =>
                `flex items-center gap-2 px-4 py-2 rounded-lg transition-colors ${
                  isActive
                    ? "bg-blue-50 text-blue-600"
                    : "text-gray-700 hover:bg-gray-50"
                }`
              }
            >
              <Calendar className="w-4 h-4" />
              <span className="text-sm">Weekly Schedule</span>
            </NavLink>
            <NavLink
              to="/siswa/subjects"
              className={({ isActive }) =>
                `flex items-center gap-2 px-4 py-2 rounded-lg transition-colors ${
                  isActive
                    ? "bg-blue-50 text-blue-600"
                    : "text-gray-700 hover:bg-gray-50"
                }`
              }
            >
              <BookOpen className="w-4 h-4" />
              <span className="text-sm">My Subjects</span>
            </NavLink>
          </nav>

          <div className="flex items-center gap-4">
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center">
                <User className="w-5 h-5 text-blue-600" />
              </div>
              <div className="hidden sm:block">
                <p className="text-sm text-gray-900">{studentName}</p>
                <p className="text-xs text-gray-500">{studentClass}</p>
              </div>
            </div>

            <button
              onClick={handleLogout}
              className="p-2 hover:bg-gray-100 rounded-lg text-gray-600"
              title="Logout"
            >
              <LogOut className="w-5 h-5" />
            </button>
          </div>
        </div>
      </div>
    </header>
  );
}
