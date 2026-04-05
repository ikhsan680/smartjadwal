import { useState } from "react";
import { Plus, Edit, Trash2, Search } from "lucide-react";

interface Teacher {
  id: number;
  name: string;
  nip: string;
  email: string;
  phone: string;
}

export function TeachersPage() {
  const [teachers] = useState<Teacher[]>([
    { id: 1, name: "John Doe", nip: "198501012010011001", email: "john.doe@school.com", phone: "+62 812-3456-7890" },
    { id: 2, name: "Jane Smith", nip: "198705152012012002", email: "jane.smith@school.com", phone: "+62 813-4567-8901" },
    { id: 3, name: "Bob Wilson", nip: "198903202014011003", email: "bob.wilson@school.com", phone: "+62 814-5678-9012" },
    { id: 4, name: "Alice Brown", nip: "199002102015012004", email: "alice.brown@school.com", phone: "+62 815-6789-0123" },
    { id: 5, name: "Charlie Davis", nip: "198808252013011005", email: "charlie.davis@school.com", phone: "+62 816-7890-1234" },
    { id: 6, name: "David Lee", nip: "199104122016012006", email: "david.lee@school.com", phone: "+62 817-8901-2345" },
    { id: 7, name: "Emma White", nip: "198612082011012007", email: "emma.white@school.com", phone: "+62 818-9012-3456" },
    { id: 8, name: "Frank Green", nip: "199206182017011008", email: "frank.green@school.com", phone: "+62 819-0123-4567" },
  ]);

  const [searchTerm, setSearchTerm] = useState("");

  const filteredTeachers = teachers.filter((teacher) =>
    teacher.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    teacher.nip.includes(searchTerm) ||
    teacher.email.toLowerCase().includes(searchTerm.toLowerCase())
  );

  return (
    <div>
      <div className="mb-8">
        <h1 className="text-3xl text-gray-900 mb-2">Teachers Management</h1>
        <p className="text-gray-600">Manage teacher information and assignments</p>
      </div>

      <div className="bg-white rounded-xl shadow-sm border border-gray-100">
        <div className="p-6 border-b border-gray-200">
          <div className="flex flex-col sm:flex-row gap-4 justify-between">
            <div className="relative flex-1 max-w-md">
              <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
              <input
                type="text"
                placeholder="Search teachers..."
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-600 focus:border-transparent outline-none"
              />
            </div>
            <button className="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2 whitespace-nowrap">
              <Plus className="w-5 h-5" />
              Add Teacher
            </button>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead className="bg-gray-50">
              <tr>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Name</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">NIP</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Email</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Phone</th>
                <th className="px-6 py-3 text-left text-xs text-gray-600 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
              {filteredTeachers.map((teacher) => (
                <tr key={teacher.id} className="hover:bg-gray-50">
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{teacher.name}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{teacher.nip}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{teacher.email}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{teacher.phone}</td>
                  <td className="px-6 py-4 whitespace-nowrap text-sm">
                    <div className="flex gap-2">
                      <button className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg">
                        <Edit className="w-4 h-4" />
                      </button>
                      <button className="p-2 text-red-600 hover:bg-red-50 rounded-lg">
                        <Trash2 className="w-4 h-4" />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {filteredTeachers.length === 0 && (
          <div className="p-8 text-center text-gray-500">
            No teachers found matching your search.
          </div>
        )}
      </div>
    </div>
  );
}
