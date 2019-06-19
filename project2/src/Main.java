import java.io.*;
import java.util.*;

public class Main {

	static Scanner in;
	static PrintStream ps;
	static RandomAccessFile sysCatalog;
	static int numOfTypes=0;
	public static void main(String[] args) throws IOException {
		in=new Scanner(new File(args[0]));
		ps = new PrintStream(new File(args[1]));
		sysCatalog= new RandomAccessFile("sysCatalog.txt","rw");
		if(sysCatalog.length()==0) {
			byte[] numOfTables=new byte[4];
			numOfTables=intToByte(0);
			sysCatalog.seek(0);
			sysCatalog.write(numOfTables);
		}
		byte[] numberOfTypes= new byte[4];
		sysCatalog.read(numberOfTypes,0,4);
		numOfTypes=byteToInt(numberOfTypes);

		while(in.hasNext()) {
			String first= in.next();
			if(first.equals("create")) {
				String second=in.next();
				if(second.equals("type")) {
					createType();
				}
				else if(second.equals("record")) {
					createRecord();
				}
			}
			else if(first.equals("delete")) {
				String second=in.next();
				if(second.equals("type")) {
					String typeName=in.next();
					deleteType(typeName);

				}
				else if(second.equals("record")) {
					deleteRecord();
				}
			}
			else if(first.equals("list")) {
				String second=in.next();
				if(second.equals("type")) {
					listType();

				}
				else if(second.equals("record")) {
					listRecord();
				}
			}
			else if(first.equals("update")) {
				in.next();
				updateRecord();
			}
			else if(first.equals("search")) {
				in.next();
				searchRecord();
			}
		}
		in.close();
	} 


	//!!!!!!! pagelerin pointerları boş kaldı
	public static boolean createType() throws IOException {
		String typeName=in.next();
		int fieldNum=in.nextInt();
		String[] arr=new String[fieldNum];
		for(int i=0;i<fieldNum;i++) {
			arr[i]=in.next();
		} //burada kontrol gerekiyor önceden açılmış mı diye
		numOfTypes++;
		byte[] table=new byte[116];
		table=stringToByte(typeName,table, 0, 10);
		table=integerToByte(fieldNum, table,10);
		for(int i=0;i<fieldNum;i++) {
			table=stringToByte(arr[i],table, 14+i*10,10);
		}
		table[114]=0; //canRead
		table[115]=1; //canWrite
		sysCatalog.seek(0);
		sysCatalog.write(intToByte(numOfTypes));
		sysCatalog.seek((numOfTypes-1)*116+4);
		sysCatalog.write(table);

		RandomAccessFile typeNew= new RandomAccessFile(typeName+"_file.txt","rw");
		byte[] fileHeader = new byte[16];
		fileHeader=stringToByte(typeName, fileHeader, 0, 10);
		fileHeader=integerToByte(1, fileHeader, 10);
		fileHeader[14]=0; //isEmpty
		fileHeader[15]=0; //hasNext
		typeNew.seek(0);
		typeNew.write(fileHeader);//typeName 10 byte, pageCount 4byte , canRead 1byte, canWrite1 byte
		byte[] page=new byte[1004];
		int pageid=0;
		for(int i=0;i<10;i++) { //10 pages boş page yaratma
			//page header pageid 4 bytes, pointer to next page 4 bytes, record count 4bytes, canRead1 canWrite 1 byte her page normalde 1Kb
			typeNew.seek(16+i*1004);
			page=integerToByte(pageid, page, 0);
			pageid++;
			page=integerToByte(pageid,page,4);
			page=integerToByte(0,page,8);
			page[12]=0; //canRead
			page[13]=1; //canWrite
			typeNew.write(page);
			for(int k=0;k<22;k++){
				typeNew.seek(1004*i+16+45*k);
				byte[] recordByte=new byte[45];
				recordByte[4]=0;
				typeNew.write(recordByte);
			}
		}
		return true;
	}
	public static boolean deleteType(String typeName) throws IOException {
		//birden fazla file olma durumunu düşünmelisin!!
		byte[] table=new byte[116];
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String name=byteToString(table,0,10);
			name=name.trim();
			if(name.equals(typeName.trim())) {
				table[114]=1; //empty yap
				table[115]=0; //doesn't have next
				File file = new File(name+"_file.txt");
				file.delete();
			}
			sysCatalog.seek(i*116+4);
			sysCatalog.write(table);
		}

		return true;
	}
	public static boolean listType() throws IOException {
		byte[] table=new byte[116];
		Vector<String> strArr=new Vector<String>();
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String typeName=byteToString(table,0,10);
			byte isEmpty=table[114]; //doluysa yazdır
			if(isEmpty==0) { //ascending sırala
				strArr.add(typeName);
			}
		}
		Collections.sort(strArr);  
		for(int a=0;a<strArr.size();a++) {
			ps.println(strArr.get(a).trim());
		}
		return true;

	}
	public static boolean createRecord() throws IOException {
		byte[] table=new byte[116];
		int fieldNum=0;
		boolean typeExists=false;
		boolean deletedFound=false;
		String name=in.next();
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String typeName=byteToString(table,0,10);
			typeName=typeName.trim();
			if(name.equals(typeName) && table[114]==0) { //empty değilse bak
				fieldNum=byteToInteger(table,10);
				typeExists=true;
				break;
			}
		}
		int[] fields=new int[fieldNum];
		for(int i=0;i<fieldNum;i++) {
			fields[i]=in.nextInt();
		}
		if(typeExists) {
			byte[] recordByte=new byte[45];
			byte[] fileHeader = new byte[16];
			byte[] pageHeader= new byte[14];
			RandomAccessFile type;
			String originalName=name;
			int count=0;
			do {
				if(count==0)
					type= new RandomAccessFile(name+"_file.txt","rw");
				else {
					name=originalName+"$"+count;
					type= new RandomAccessFile(name+"_file.txt","rw");
				}
				type.seek(0);
				type.readFully(fileHeader, 0,16);
				int pageCount=byteToInteger(fileHeader,10);
			
				for(int i=0;i<10&&!deletedFound;i++) {
					type.seek(1004*i+16); //sayfa dolaşıyoruz 
					type.readFully(pageHeader,0,14);
					int recordCount=byteToInteger(pageHeader,8);
					if(recordCount<22) {
						//buraya neden girmiyor???
						for(int n=0;n<22&&!deletedFound;n++) {
							type.seek(1004*i+30+45*n);
							type.readFully(recordByte,0,45);
							if(recordByte[4]==0) { //invalid bulundu
								deletedFound=true;
								recordByte=integerToByte(i*22+n,recordByte,0);
								recordByte[4]=1;
								for(int k=0;k<fieldNum;k++){
									recordByte=integerToByte(fields[k],recordByte,5+k*4);
								}
								type.seek(1004*i+30+45*n);
								type.write(recordByte);
								if(recordCount==0) { //yeni page aç(kullanılan page sayısını artır)
									int oldPageNum=byteToInteger(fileHeader,10);
									if(oldPageNum<10) { //10dan fazla page olamaz
										fileHeader=integerToByte(oldPageNum+1,fileHeader,10);
										type.seek(0);
										type.write(fileHeader);
									}
								}
								//recordCount artır
								type.seek(1004*i+16);
								pageHeader=integerToByte(recordCount+1,pageHeader,8);
								type.write(pageHeader);
								
							}
						}
					}

				}
				count++;
			}
			while(fileHeader[15]==1&&!deletedFound);
			if(!deletedFound) { //yeni file yaratıyoruz
				name=name+"$"+count;
				fileHeader[115]=1;
				type.seek(0);
				type.write(fileHeader);
				type= new RandomAccessFile(name+"_file.txt","rw");
				byte[] fileHeaderNew = new byte[16];
				fileHeaderNew=stringToByte(name, fileHeaderNew, 0, 10);
				fileHeaderNew=integerToByte(0, fileHeaderNew, 10);
				fileHeaderNew[14]=0; //isEmpty
				fileHeaderNew[15]=0; //hasNext
				type.seek(0);
				type.write(fileHeaderNew);//typeName 10 byte, pageCount 4byte , canRead 1byte, canWrite1 byte
				byte[] page=new byte[1004];
				int pageid=0;
				for(int i=0;i<10;i++) { //10 pages boş page yaratma
					//page header pageid 4 bytes, pointer to next page 4 bytes, record count 4bytes, canRead1 canWrite 1 byte her page normalde 1Kb
					type.seek(16+i*1004);
					page=integerToByte(pageid, page, 0);
					pageid++;
					page=integerToByte(pageid,page,4);
					page=integerToByte(0,page,8);
					page[12]=0; //canRead
					page[13]=1; //canWrite
					type.write(page);
					for(int k=0;k<22;k++){
						type.seek(1004*i+16+45*k);
						byte[] recordByteNew=new byte[45];
						recordByte[4]=0;
						type.write(recordByteNew);
					}
				}
				type.seek(30);
				type.readFully(recordByte,0,45);
				//yeniyi ekliyor
				recordByte[4]=1;
				for(int k=0;k<fieldNum;k++){
					recordByte=integerToByte(fields[k],recordByte,5+k*4);
				}
				type.seek(0);
				type.write(recordByte);
			}
			type.close();
		}

		return true;
	}
	public static boolean deleteRecord() throws IOException {
		//burada kontrol etmek lazım has next şeyini
		byte[] table=new byte[116];
		String name=in.next();
		int deletedPrimary=in.nextInt();
		byte[] recordByte=new byte[45];
		RandomAccessFile type;
		boolean found=false;
		//burada yine bir kontrol gerekiyor page sayısı kontrolü unutma!!
		byte[] fileHeader = new byte[16];
		byte[] pageHeader= new byte[14];
		String originalName=name;
		int count=0;
		boolean typeExists=false;
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String typeName=byteToString(table,0,10);
			typeName=typeName.trim();
			if(name.equals(typeName) && table[114]==0) { //empty değilse bak
				typeExists=true;
				break;
			}
		}
		if(typeExists) {
			do {
				if(count==0)
					type= new RandomAccessFile(name+"_file.txt","rw");
				else {
					name=originalName+"$"+count;
					type= new RandomAccessFile(name+"_file.txt","rw");
				}
				type.seek(0);
				type.readFully(fileHeader, 0,16);
				int pageCount=byteToInteger(fileHeader,10);
				//önce olan pagelerde boşluk var mı diye bak silinmiş varsa yenile
				//ilkinde hepsini silinmiş olarak alıyoruz o yüzden sorun olmuyor
				//yoksa ve hepsi doluysa yeni page aç pageler açılı ama record 
				//sayısına bakmıyouz onları update etmek lazım
				for(int i=0;i<10&&!found;i++) {
					type.seek(1004*i+16);
					type.readFully(pageHeader,0,14);
					int recordCount=byteToInteger(pageHeader,8);
					if(recordCount>0) {
						for(int n=0;n<22&&!found;n++) {
							type.seek(1004*i+30+45*n);
							type.readFully(recordByte,0,45);
							int primaryKey=byteToInteger(recordByte,5);
							if(primaryKey==deletedPrimary) {
								found=true;
								recordByte[4]=0;
								type.seek(1004*i+30+45*n);
								type.write(recordByte);
								//pageHeaderdaki recordları azalttık
								recordCount--;
								pageHeader=integerToByte(recordCount,pageHeader,8);
								type.seek(1004*i+16);
								type.write(pageHeader);
							}
						}	
					}
					if(found&&recordCount==0) {
						if(pageCount>1)
							pageCount=pageCount-1;

						type.seek(0);
						fileHeader=integerToByte(pageCount,fileHeader,10);
						type.write(fileHeader);

					}
				}
				type.close();
			}
			while(fileHeader[15]==1&&!found);
		}
		return true;
	}
	public static boolean updateRecord() throws IOException {
		byte[] table=new byte[116];
		int fieldNum=0;
		String name=in.next();
		boolean typeExists=false;
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String typeName=byteToString(table,0,10);
			typeName=typeName.trim();
			if(name.equals(typeName)) {
				fieldNum=byteToInteger(table,10);
				typeExists=true;
			}
		}
		int[] fields=new int[fieldNum];
		for(int i=0;i<fieldNum;i++) {
			fields[i]=in.nextInt();
		}
		if(typeExists) {
			byte[] fileHeader = new byte[16];
			byte[] pageHeader= new byte[14];
			int count=0;
			boolean found=false;
			RandomAccessFile type;
			String originalName=name;
			do {
				if(count==0)
					type= new RandomAccessFile(name+"_file.txt","rw");
				else {
					name=originalName+"$"+count;
					type= new RandomAccessFile(name+"_file.txt","rw");
				}
				byte[] recordByte=new byte[45];
				type.seek(0);
				type.readFully(fileHeader, 0,16);
				int pageCount=byteToInteger(fileHeader,10);
				for(int i=0;i<10&&!found;i++) {
					type.seek(1004*i+16);
					type.readFully(pageHeader,0,14);
					int recordCount=byteToInteger(pageHeader,8);
					if(recordCount>0) {
						for(int n=0;n<22&&!found;n++) {
							type.seek(1004*i+30+45*n);
							type.readFully(recordByte,0,45);
							int primaryKey=byteToInteger(recordByte,5);
							if(primaryKey==fields[0] && recordByte[4]==1) {
								found=true;
								for(int k=0;k<fieldNum;k++){
									recordByte=integerToByte(fields[k],recordByte,5+k*4);
								}
								type.seek(1004*i+30+45*n);
								type.write(recordByte);
							}
						}
					}


				} 
				type.close();
			}
			while(fileHeader[15]==1&&!found);
		}
		return true;
	}
	public static boolean searchRecord() throws IOException {
		byte[] table=new byte[116];
		boolean typeExists=false;
		int fieldNum=0;
		String name=in.next();
		int primary=in.nextInt();
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String typeName=byteToString(table,0,10);
			typeName=typeName.trim();
			if(name.equals(typeName) && table[114]==0) {
				fieldNum=byteToInteger(table,10);
				typeExists=true;
			}
		}
		boolean found=false;
		if(typeExists) {
			int[] fields=new int[fieldNum];
			byte[] recordByte=new byte[45];
			int count=0;
			byte[] fileHeader = new byte[16];
			byte[] pageHeader= new byte[14];
			RandomAccessFile type;
			String originalName=name;
			do {
				if(count==0)
					type= new RandomAccessFile(name+"_file.txt","rw");
				else {
					name=originalName+"$"+count;
					type= new RandomAccessFile(name+"_file.txt","rw");
				}

				type.seek(0);
				type.readFully(fileHeader, 0,16);
				int pageCount=byteToInteger(fileHeader,10);

				for(int i=0;i<10&&!found;i++) {
					type.seek(1004*i+16);
					type.readFully(pageHeader,0,14);
					int recordCount=byteToInteger(pageHeader,8);
					if(recordCount>0) {
						for(int n=0;n<22&&!found;n++) {
							type.seek(1004*i+30+45*n);
							type.readFully(recordByte,0,45);
							int primaryKey=byteToInteger(recordByte,5);
							if(primaryKey==primary && recordByte[4]==1) {
								found=true;
								for(int k=0;k<fieldNum;k++){
									fields[k]=byteToInteger(recordByte,5+k*4);
								}
							}
						}
					}
				}
			}
			while(fileHeader[15]==1&&!found);

			if(found) {
				for(int i=0;i<fieldNum-1;i++) {
					ps.print(fields[i]+" ");
				}
				ps.println(fields[fieldNum-1]);
			}
		}

		return true;
	}
	public static boolean listRecord() throws IOException {
		//primary key sırasına göre bas!!!!!
		Vector<String> records =new Vector<String>();
		byte[] table=new byte[116];
		int fieldNum=0;
		boolean typeExists=false;
		String name=in.next();
		sysCatalog.seek(0);
		for(int i=0;i<numOfTypes;i++) {
			sysCatalog.seek(i*116+4);
			sysCatalog.readFully(table, 0,116);
			String typeName=byteToString(table,0,10);
			typeName=typeName.trim();
			if(name.equals(typeName) && table[114]==0) {
				fieldNum=byteToInteger(table,10);
				typeExists=true;
				break;
			}
		}
		if(typeExists) {
			int[] fields=new int[fieldNum];
			byte[] recordByte=new byte[45];

			byte[] fileHeader = new byte[16];
			byte[] pageHeader= new byte[14];
			int count=0;
			RandomAccessFile type;
			String originalName=name;
			do {
				if(count==0)
					type= new RandomAccessFile(name+"_file.txt","rw");
				else {
					name=originalName+"$"+count;
					type= new RandomAccessFile(name+"_file.txt","rw");
				}

				type.seek(0);
				type.readFully(fileHeader, 0,16);
				int pageCount=byteToInteger(fileHeader,10);
				for(int i=0;i<10;i++) {
					type.seek(1004*i+16);
					type.readFully(pageHeader,0,14);
					int recordCount=byteToInteger(pageHeader,8);
					if(recordCount>0) {
						for(int n=0;n<22;n++) {
							type.seek(1004*i+30+45*n);
							type.readFully(recordByte,0,45);
							int primaryKey=byteToInteger(recordByte,5);
							if(recordByte[4]==1) {
								for(int k=0;k<fieldNum;k++){
									fields[k]=byteToInteger(recordByte,5+k*4);
								}
								String str="";
								for(int k=0;k<fieldNum-1;k++) {
									str+=fields[k]+" ";
								}
								str+=fields[fieldNum-1];
								records.add(str);
							}
						}
					}

				}

			}
			while(fileHeader[15]==1);
		}
		Collections.sort(records, new Comparator<String>() {
			@Override
			public int compare(String o1, String o2) {
				int end1=o1.length()-1;
				if(o1.contains(" "))
					end1=o1.indexOf(' ');
				int end2=o2.length()-1;
				if(o2.contains(" "))
					end2=o2.indexOf(' ');
				int a1=Integer.parseInt(o1.substring(0,end1 ));
				int a2=Integer.parseInt(o2.substring(0, end2));
				return a1- a2;
			}

		});
		for(int i=0;i<records.size();i++) {
			ps.println(records.get(i));
		}
		return true;
	}
	public static byte[] intToByte(int i ) {
		byte[] result = new byte[4];

		result[0] = (byte) (i >> 24);
		result[1] = (byte) (i >> 16);
		result[2] = (byte) (i >> 8);
		result[3] = (byte) (i /*>> 0*/);

		return result;
	}
	public static byte[] stringToByte(String str,byte[] byteArr, int start, int length) {
		//stryi byteArra ekle starttan başla length kadar ilerle
		byte[] strArr=new byte[length];
		for(int i=str.length();i<length;i++) {
			str+=" ";
		}
		strArr=str.getBytes();
		for(int i=0;i<length;i++) {
			byteArr[start+i]=strArr[i];
		}
		return byteArr;
	}
	public static byte[] integerToByte(int n,byte[] byteArr, int start) {
		//stryi byteArra ekle starttan başla length kadar ilerle
		byte[] intArr=new byte[4];
		intArr=intToByte(n);
		for(int i=0;i<4;i++) {
			byteArr[start+i]=intArr[i];
		}
		return byteArr;
	}

	public static int byteToInteger(byte[] byteArr,int start) {
		byte[] arr=new byte[4];
		for(int i=0;i<4;i++) {
			arr[i]=byteArr[i+start];
		}
		return byteToInt(arr);
	}
	public static int byteToInt(byte[] bytes) {
		return bytes[0] << 24 | (bytes[1] & 0xFF) << 16 | (bytes[2] & 0xFF) << 8 | (bytes[3] & 0xFF);
	}
	public static String byteToString(byte[] byteArr, int start, int length) {
		byte[] arr=new byte[length];
		for(int i=0;i<length;i++) {
			arr[i]=byteArr[i+start];
		}
		return new String(arr);
	}
}
