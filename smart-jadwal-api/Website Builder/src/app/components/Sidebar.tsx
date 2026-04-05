import { NavLink } from "react-router-dom";
import { 
  LayoutDashboard, 
  Calendar, 
  Users, 
  BookOpen, 
  School,
  GraduationCap,
  CalendarDays
} from "lucide-react";

export function Sidebar() {
  const navItems = [
    { path: "/guru/dashboard", icon: LayoutDashboard, label: "Dashboard" },
    { path: "/guru/schedules", icon: Calendar, label: "Schedules" },
    { path: "/guru/calendar", icon: CalendarDays, label: "Calendar View" },
    { path: "/guru/teachers", icon: Users, label: "Teachers" },
    { path: "/guru/classes", icon: School, label: "Classes" },
    { path: "/guru/subjects", icon: BookOpen, label: "Subjects" },
  ];

  return (
    <aside className="w-64 bg-white border-r border-gray-200 min-h-screen fixed left-0 top-0">
      <div className="p-6 border-b border-gray-200">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
            <GraduationCap className="w-6 h-6 text-white" />
          </div>
          <div>
            <h2 className="text-lg text-gray-900">Smart Jadwal</h2>
            <p className="text-xs text-gray-500">School System</p>
          </div>
        </div>
      </div>

      <nav className="p-4">
        <ul className="space-y-2">
          {navItems.map((item) => (
            <li key={item.path}>
              <NavLink
                to={item.path}
                className={({ isActive }) =>
                  `flex items-center gap-3 px-4 py-3 rounded-lg transition-colors ${
                    isActive
                      ? "bg-blue-50 text-blue-600"
                      : "text-gray-700 hover:bg-gray-50"
                  }`
                }
              >
                <item.icon className="w-5 h-5" />
                <span>{item.label}</span>
              </NavLink>
            </li>
          ))}
        </ul>
      </nav>
    </aside>
  );
}
