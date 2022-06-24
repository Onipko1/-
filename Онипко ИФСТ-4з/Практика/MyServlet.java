package com.wes;

import java.io.IOException;
import java.io.PrintWriter;
import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

@WebServlet("/MyServlet")
public class MyServlet extends HttpServlet 
{
	private static final long serialVersionUID = 1L;
   
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException 
	{

		PrintWriter out = response.getWriter();
		response.setContentType("text/html");
		out.println("<html>"); 
		out.println("<head>");
		
		out.println("<title> MyServlet</title>"); 
		out.println("</head>"); 
		out.println("<body>"); 
		out.println("<h1>Reservation</h1>");
		out.println(request.getParameter("Name") + "'s You has been reserved."); 
		out.println("</body>"); 
		out.println("</html>");	
			
	}

}
