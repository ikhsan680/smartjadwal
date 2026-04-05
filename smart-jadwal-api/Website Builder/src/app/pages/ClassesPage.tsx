import { useState } from "react";
import { Plus, Edit, Trash2, Users } from "lucide-react";

interface Class {
  id: number;
  name: string;
  major: string;
  grade: string;
  totalStudents: number;
  homeroom: string;
}

export function ClassesPage() {
  const [classes] = useState<Class[]>([
    { id: 1, name: "X RPL 1", major: "Software Engineering", grade: "10", totalStudents: 32, homeroom: "John Doe" },
    { id: 2, name: "X RPL 2", major: "Software Engineering", grade: "10", totalStudents: 30, homeroom: "Jane Smith" },
    { id: 3, name: "XI RPL 1", major: "Software Engineering", grade: "11", totalStudents: 31, homeroom: "Bob Wilson" },
    { id: 4, name: "XI RPL 2", major: "Software Engineering", grade: "11", totalStudents: 29, homeroom: "Alice Brown" },
    { id: 5, name: "XII RPL 1", major: "Software Engineering", grade: "12", totalStudents: 28, homeroom: "Charlie Davis" },
    { id: 6, name: "XII RPL 2", major: "Software Engineering", grade: "12", totalStudents: 30, homeroom: "David Lee" },
    { id: 7, name: "X TKJ 1", major: "Computer Network", grade: "10", totalStudents: 33, homeroom: "Emma White" },
    { id: 8, name: "XI TKJ 1", major: "Computer Network", grade: "11", totalStudents: 31, homeroom: "Frank Green" },
  ]);

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Classes Management</h1>
        <p className="text-gray-600">Manage school classes and student groups</p>
      </div>

      <div className="mb-6 flex justify-end">
        <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
          <Plus className="w-5 h-5" />
          Add Class
        </button>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {classes.map((classItem) => (
          <div key={classItem.id} className="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
            <div className="flex items-start justify-between mb-4">
              <div>
                <h3 className="text-xl text-gray-900 mb-1">{classItem.name}</h3>
                <p className="text-sm text-gray-600">{classItem.major}</p>
              </div>
              <div className="flex gap-2">
                <button className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                  <Edit className="w-4 h-4" />
                </button>
                <button className="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                  <Trash2 className="w-4 h-4" />
                </button>
              </div>
            </div>

            <div className="space-y-3">
              <div className="flex items-center gap-2 text-sm">
                <span className="text-gray-600">Grade:</span>
                <span className="text-gray-900">{classItem.grade}</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <Users className="w-4 h-4 text-gray-400" />
                <span className="text-gray-600">Students:</span>
                <span className="text-gray-900">{classItem.totalStudents}</span>
              </div>
              <div className="flex items-center gap-2 text-sm">
                <span className="text-gray-600">Homeroom:</span>
                <span className="text-gray-900">{classItem.homeroom}</span>
              </div>
            </div>

            <div className="mt-4 pt-4 border-t border-gray-100">
              <button className="text-sm text-blue-600 hover:text-blue-700">
                View Details →
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}
